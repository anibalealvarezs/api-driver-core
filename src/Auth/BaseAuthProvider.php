<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Auth;

use Anibalealvarezs\ApiDriverCore\Interfaces\AuthProviderInterface;

abstract class BaseAuthProvider implements AuthProviderInterface
{
    protected array $data = [];
    protected string $filePath = "";

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
            $dir = dirname($this->filePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($this->filePath, json_encode($this->data, JSON_PRETTY_PRINT));
        }
    }

    public function isValid(): bool
    {
        return !empty($this->getAccessToken());
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
}
