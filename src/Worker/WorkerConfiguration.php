<?php

declare(strict_types=1);

namespace MAAF\Queue\Worker;

/**
 * Worker Configuration
 * 
 * Worker konfiguráció modulonként.
 * 
 * @version 1.0.0
 */
final class WorkerConfiguration
{
    public function __construct(
        private readonly string $moduleName,
        private readonly string $queue,
        private readonly int $processes = 1,
        private readonly int $timeout = 60,
        private readonly int $memory = 128,
        private readonly int $sleep = 3,
        private readonly int $maxTries = 3,
        private readonly bool $force = false
    ) {
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function getQueue(): string
    {
        return $this->queue;
    }

    public function getProcesses(): int
    {
        return $this->processes;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getMemory(): int
    {
        return $this->memory;
    }

    public function getSleep(): int
    {
        return $this->sleep;
    }

    public function getMaxTries(): int
    {
        return $this->maxTries;
    }

    public function isForce(): bool
    {
        return $this->force;
    }

    /**
     * Convert to array
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'module' => $this->moduleName,
            'queue' => $this->queue,
            'processes' => $this->processes,
            'timeout' => $this->timeout,
            'memory' => $this->memory,
            'sleep' => $this->sleep,
            'maxTries' => $this->maxTries,
            'force' => $this->force,
        ];
    }
}
