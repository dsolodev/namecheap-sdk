<?php

declare(strict_types = 1);

namespace Namecheap\Response;

use JsonException;

final readonly class NamecheapResponse implements ApiResponse
{
    public function __construct(
        private array  $data,
        private bool   $success,
        private array  $errors,
        private array  $warnings,
        private string $command,
        private float  $executionTime,
        private string $rawXml,
        private array  $meta = []
    ) {}

    public function getData(): array
    {
        return $this->data;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getExecutionTime(): float
    {
        return $this->executionTime;
    }

    public function getRawXml(): string
    {
        return $this->rawXml;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @throws JsonException
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }

    public function toArray(): array
    {
        return [
            'success'  => $this->success,
            'data'     => $this->data,
            'errors'   => $this->errors,
            'warnings' => $this->warnings,
            'meta'     => array_merge($this->meta, [
                'command'        => $this->command,
                'execution_time' => $this->executionTime,
            ]),
            'raw'      => $this->rawXml,
        ];
    }

    public function toXml(): string
    {
        return $this->rawXml;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    public function getFirstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    public function getRequestId(): ?string
    {
        return $this->meta['request_id'] ?? null;
    }

    public function getServer(): ?string
    {
        return $this->meta['server'] ?? null;
    }
}