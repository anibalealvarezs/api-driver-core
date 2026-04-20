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
    public ?string $canonicalId = null;
    public ?string $title = null;
    public ?string $url = null;
    public ?string $hostname = null;
    public ?string $type = null;
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

    public function getPlatformId(): ?string { return $this->platformId; }
    public function setPlatformId(?string $id): self { $this->platformId = $id; return $this; }

    public function getCanonicalId(): ?string { return $this->canonicalId; }
    public function setCanonicalId(?string $id): self { $this->canonicalId = $id; return $this; }

    public function getTitle(): ?string { return $this->title; }
    public function setTitle(?string $title): self { $this->title = $title; return $this; }

    public function getUrl(): ?string { return $this->url; }
    public function setUrl(?string $url): self { $this->url = $url; return $this; }

    public function getHostname(): ?string { return $this->hostname; }
    public function setHostname(?string $hostname): self { $this->hostname = $hostname; return $this; }

    public function getType(): ?string { return $this->type; }
    public function setType(?string $type): self { $this->type = $type; return $this; }

    public function getData(): array { return $this->data; }
    public function setData(array $data): self { $this->data = $data; return $this; }

    public function getChannel(): mixed { return $this->channel; }
    public function setChannel(mixed $channel): self { $this->channel = $channel; return $this; }

    public function getPlatformCreatedAt(): ?\Carbon\Carbon { return $this->platformCreatedAt; }
    public function setPlatformCreatedAt(?\Carbon\Carbon $date): self { $this->platformCreatedAt = $date; return $this; }

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

    public function __toString(): string
    {
        return (string) ($this->platformId ?? ($this->canonicalId ?? ''));
    }
}
