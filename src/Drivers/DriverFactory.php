<?php

    namespace Anibalealvarezs\ApiDriverCore\Drivers;

    use Anibalealvarezs\ApiDriverCore\Interfaces\SyncDriverInterface;
    use Anibalealvarezs\ApiDriverCore\Helpers\Helpers;
    use Exception;
    use Psr\Log\LoggerInterface;
    use ReflectionClass;
    use ReflectionException;

    class DriverFactory
    {
        private static array $instances = [];

        /**
         * Limpia todas las instancias y el registro (útil para testing).
         */
        public static function reset(): void
        {
            self::$instances = [];
            self::$registry = [];
        }

        /**
         * Mapeo de canales a sus respectivas clases de Driver y AuthProvider.
         */
        private static array $registry = [];

        /**
         * Carga el registro de drivers desde el archivo de configuración.
         */
        private static function loadRegistry(): void
        {
            if (!empty(self::$registry)) {
                return;
            }

            $configDir = getenv('CONFIG_DIR') ?: __DIR__.'/../../../config';
            $filePath = $configDir.'/drivers.yaml';

            if (file_exists($filePath)) {
                $yamlConfig = \Symfony\Component\Yaml\Yaml::parseFile($filePath);
                if (is_array($yamlConfig)) {
                    self::$registry = $yamlConfig;
                }
            }
        }

        /**
         * Get the full driver registry.
         */
        public static function getRegistry(): array
        {
            self::loadRegistry();

            return self::$registry;
        }

        /**
         * Get registry info for a specific channel.
         */
        public static function getChannelConfig(string $channel): array
        {
            self::loadRegistry();

            return self::$registry[$channel] ?? [];
        }

        /**
         * Obtiene una instancia del driver para el canal especificado.
         *
         * @param string $channel
         * @param LoggerInterface|null $logger
         * @param array $config
         * @return SyncDriverInterface
         * @throws ReflectionException
         * @throws Exception
         */
        public static function get(string $channel, ?LoggerInterface $logger = null, array $config = []): SyncDriverInterface
        {
            self::loadRegistry();

            if (isset(self::$instances[$channel]) && empty($config)) {
                return self::$instances[$channel];
            }

            if (!isset(self::$registry[$channel])) {
                throw new Exception("Driver not found for channel: $channel");
            }

            $regConfig = self::$registry[$channel];
            $driverClass = $regConfig['driver'] ?? null;
            $authProviderClass = $regConfig['auth'] ?? null;

            if (!$driverClass) {
                throw new Exception("Driver class not specified for channel: $channel");
            }

            if (!class_exists($driverClass)) {
                throw new Exception("Driver class not found: $driverClass");
            }

            // Resilient construction for legacy and modular providers
            if (empty($config)) {
                $allConfigs = Helpers::getChannelsConfig();
                $channelConfig = $allConfigs[$channel] ?? [];

                // Merge common configurations if specified by the driver
                $commonKey = $driverClass::getCommonConfigKey();
                if ($commonKey && isset($allConfigs[$commonKey])) {
                    $channelConfig = array_merge($allConfigs[$commonKey], $channelConfig);
                }
            } else {
                $channelConfig = $config;
            }

            if ($authProviderClass) {
                $reflection = new ReflectionClass($authProviderClass);
                $constructor = $reflection->getConstructor();

                if ($constructor && isset($constructor->getParameters()[0])) {
                    $firstParam = $constructor->getParameters()[0];
                    $type = $firstParam->getType();
                    if ($type instanceof \ReflectionNamedType && $type->getName() === 'string') {
                        $authProvider = new $authProviderClass($channelConfig['token_path'] ?? "");
                    } else {
                        $authProvider = new $authProviderClass($channelConfig);
                    }
                } else {
                    $authProvider = new $authProviderClass($channelConfig);
                }

                if (method_exists($authProvider, 'setTokenRefresherCallback')) {
                    $authProvider->setTokenRefresherCallback(self::createTokenRefresherCallback($channel, $logger));
                }

                $driver = new $driverClass($authProvider, $logger);
            } else {
                $driver = new $driverClass(null, $logger);
            }

            // Ensure configuration is validated and normalized
            $validatedConfig = $driver->validateConfig($channelConfig);
            if ($authProvider = $driver->getAuthProvider()) {
                if (method_exists($authProvider, 'setConfig')) {
                    $authProvider->setConfig($validatedConfig);
                }
            }

            $driver->boot();

            // Inject data processor if defined and supported by driver
            if (isset($regConfig['processor']) && method_exists($driver, 'setDataProcessor')) {
                $driver->setDataProcessor($regConfig['processor']);
            }

            if (empty($config)) {
                self::$instances[$channel] = $driver;
            }

            return $driver;
        }

        /**
         * Registra manualmente un nuevo driver (útil para extensiones externas).
         *
         * @param string $channel
         * @param string $driverClass
         * @param string $authClass
         */
        public static function register(string $channel, string $driverClass, string $authClass): void
        {
            self::loadRegistry();

            self::$registry[$channel] = [
                'driver' => $driverClass,
                'auth'   => $authClass,
            ];
        }

        /**
         * Fuerza una instancia para un canal (útil para testing).
         *
         * @param string $channel
         * @param SyncDriverInterface $instance
         */
        public static function setInstance(string $channel, SyncDriverInterface $instance): void
        {
            self::$instances[$channel] = $instance;
        }

        /**
         * Obtiene la lista de canales que tienen un driver registrado.
         *
         * @return string[]
         */
        public static function getAvailableChannels(): array
        {
            self::loadRegistry();

            return array_filter(array_keys(self::$registry), function ($channel) {
                return isset(self::$registry[$channel]['driver']);
            });
        }

        /**
         * Creates a resilient token refresher callback that delegates refresh to the Facade Token Authority.
         *
         * @param string $channel
         * @param LoggerInterface|null $logger
         * @return callable|null
         */
        private static function createTokenRefresherCallback(string $channel, ?LoggerInterface $logger): ?callable
        {
            $enabled = filter_var($_ENV['TOKEN_AUTHORITY_ENABLED'] ?? getenv('TOKEN_AUTHORITY_ENABLED') ?? false, FILTER_VALIDATE_BOOLEAN);
            $url = $_ENV['TOKEN_AUTHORITY_URL'] ?? getenv('TOKEN_AUTHORITY_URL');
            $bearer = $_ENV['TOKEN_AUTHORITY_BEARER'] ?? getenv('TOKEN_AUTHORITY_BEARER');

            if (!$enabled || !$url || !$bearer) {
                return null;
            }

            return function () use ($channel, $url, $bearer, $logger) {
                $maxRetries = 10;
                $baseBackoff = 30; // seconds

                for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                    try {
                        $logger?->info("Requesting new token for $channel from Token Authority (Attempt $attempt/$maxRetries)");

                        $client = new \GuzzleHttp\Client(['timeout' => 30]);
                        $response = $client->post($url, [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $bearer,
                                'Accept' => 'application/json',
                                'Content-Type' => 'application/json',
                            ],
                            'json' => [
                                'channel' => $channel,
                            ]
                        ]);

                        $data = json_decode($response->getBody()->getContents(), true);

                        if ($response->getStatusCode() === 200 && isset($data['access_token'])) {
                            $logger?->info("Successfully received new token for $channel from Token Authority.");
                            return $data['access_token'];
                        }

                        throw new Exception("Invalid response from Token Authority: " . json_encode($data));
                    } catch (Exception $e) {
                        $logger?->warning("Token Authority refresh failed for $channel on attempt $attempt: " . $e->getMessage());

                        if ($attempt === $maxRetries) {
                            $logger?->error("Token Authority refresh failed after $maxRetries attempts for $channel.");
                            throw new Exception("Failed to obtain token from Authority after $maxRetries attempts.", 0, $e);
                        }

                        // Exponential backoff
                        $sleepTime = $baseBackoff * pow(2, $attempt - 1);
                        $logger?->info("Sleeping for $sleepTime seconds before next Token Authority attempt...");
                        sleep($sleepTime);
                    }
                }

                return null;
            };
        }

        /**
         * Verifica si un canal soporta una entidad específica.
         *
         * @param string $channel
         * @param string $entity
         * @return bool
         */
        public static function supportsEntity(string $channel, string $entity): bool
        {
            self::loadRegistry();

            if (!isset(self::$registry[$channel])) {
                return false;
            }

            $supportedEntities = self::$registry[$channel]['entities'] ?? [];

            // Match both 'metric' and 'metrics', 'order' and 'orders', etc.
            $entity = strtolower($entity);
            $pluralEntity = $entity.'s';
            if (!str_ends_with($entity, 's')) {
                $pluralEntity = $entity.'s';
            } else {
                $pluralEntity = $entity;
                $entity = substr($entity, 0, -1);
            }

            return in_array($entity, $supportedEntities) || in_array($pluralEntity, $supportedEntities);
        }

        /**
         * Obtiene los canales que soportan una entidad.
         *
         * @param string $entity
         * @return string[]
         */
        public static function getAvailableChannelsForEntity(string $entity): array
        {
            self::loadRegistry();
            $channels = [];

            foreach (self::$registry as $channel => $config) {
                if (self::supportsEntity($channel, $entity)) {
                    $channels[] = $channel;
                }
            }

            return $channels;
        }
    }
