<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Auth;

use Anibalealvarezs\ApiDriverCore\Interfaces\AuthProviderInterface;

abstract class BaseAuthProvider implements AuthProviderInterface
{
    protected array $data = [];
    protected string $filePath = "";
    /** @var callable|null */
    protected $tokenRefresherCallback = null;

    public function __construct(array|string $configOrPath = "")
    {
        if (is_array($configOrPath)) {
            $this->data = $configOrPath;
            return;
        }

        $this->filePath = $configOrPath;
        $this->load();
    }

    protected function load(): void
    {
        if ($this->filePath && file_exists($this->filePath)) {
            $content = file_get_contents($this->filePath);
            if ($content) {
                $this->data = json_decode($content, true) ?: [];
            }
        }
    }

    protected function save(): void
    {
        if ($this->filePath) {
            \Anibalealvarezs\ApiDriverCore\Helpers\Helpers::writeTokenFile($this->filePath, $this->data);
        }
    }

    public function isValid(): bool
    {
        return !empty($this->getAccessToken());
    }

    public function hasCredentials(): bool
    {
        return !empty($this->data);
    }

    public function isExpired(): bool
    {
        return false;
    }

    public function refresh(): bool
    {
        // Default implementation does nothing, override if needed
        return true;
    }

    public function getScopes(): array
    {
        return [];
    }

    public function updateCredentials(array $credentials): void
    {
        $this->data = array_replace_recursive($this->data, $credentials);
        $this->save();
    }

    public function getConfig(): array
    {
        return $this->data;
    }

    public function setConfig(array $config): void
    {
        $this->data = array_replace_recursive($this->data, $config);
    }

    abstract public function getAccessToken(): string;
    abstract public function setAccessToken(string $token): void;
    abstract public function getUserId(): string;

    public function getTokenRefresherCallback(): ?callable
    {
        return $this->tokenRefresherCallback;
    }

    public function setTokenRefresherCallback(?callable $callback): void
    {
        $this->tokenRefresherCallback = $callback;
    }
}
