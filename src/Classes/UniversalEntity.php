<?php

declare(strict_types=1);

namespace Anibalealvarezs\ApiDriverCore\Classes;

/**
 * UniversalEntity
 * 
 * A standardized entity object used across drivers and the host orchestrator.
 */
class UniversalEntity
{
    public ?string $platformId = null;
    public ?\Carbon\Carbon $platformCreatedAt = null;
    public mixed $channel = null;
    public array $data = [];
    private array $context = [];

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array $context
     * @return $this
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Magic getter for property access.
     */
    public function __get(string $name): mixed
    {
        return $this->context[$name] ?? ($this->data[$name] ?? null);
    }

    /**
     * Magic setter for property access.
     */
    public function __set(string $name, mixed $value): void
    {
        $this->context[$name] = $value;
    }

    /**
     * Magic isset for property access.
     */
    public function __isset(string $name): bool
    {
        return isset($this->context[$name]) || isset($this->data[$name]);
    }
}
