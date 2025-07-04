<?php

declare(strict_types = 1);

namespace Namecheap\Response;

interface ApiResponse
{
    public function getData(): array;

    public function isSuccess(): bool;

    public function getErrors(): array;

    public function getWarnings(): array;

    public function getCommand(): string;

    public function getExecutionTime(): float;

    public function getRawXml(): string;

    public function toArray(): array;

    public function toJson(): string;

    public function toXml(): string;
}