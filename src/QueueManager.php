<?php

declare(strict_types=1);

namespace MAAF\Queue;

/**
 * Queue Manager
 * 
 * Queue kezelő több driver támogatással.
 * 
 * @version 1.0.0
 */
final class QueueManager
{
    /**
     * @var array<string, QueueDriverInterface>
     */
    private array $drivers = [];

    private string $defaultDriver = 'redis';
    private string $defaultQueue = 'default';

    /**
     * Register driver
     * 
     * @param string $name Driver name
     * @param QueueDriverInterface $driver Driver instance
     * @return void
     */
    public function registerDriver(string $name, QueueDriverInterface $driver): void
    {
        $this->drivers[$name] = $driver;
    }

    /**
     * Set default driver
     * 
     * @param string $name Driver name
     * @return void
     */
    public function setDefaultDriver(string $name): void
    {
        if (!isset($this->drivers[$name])) {
            throw new \RuntimeException("Driver not found: {$name}");
        }

        $this->defaultDriver = $name;
    }

    /**
     * Set default queue
     * 
     * @param string $queue Queue name
     * @return void
     */
    public function setDefaultQueue(string $queue): void
    {
        $this->defaultQueue = $queue;
    }

    /**
     * Get driver
     * 
     * @param string|null $name Driver name (null = default)
     * @return QueueDriverInterface
     */
    public function getDriver(?string $name = null): QueueDriverInterface
    {
        $name = $name ?? $this->defaultDriver;

        if (!isset($this->drivers[$name])) {
            throw new \RuntimeException("Driver not found: {$name}");
        }

        return $this->drivers[$name];
    }

    /**
     * Push job to queue
     * 
     * @param JobInterface $job Job instance
     * @param string|null $queue Queue name (null = default)
     * @param string|null $driver Driver name (null = default)
     * @param int $delay Delay in seconds
     * @return void
     */
    public function push(JobInterface $job, ?string $queue = null, ?string $driver = null, int $delay = 0): void
    {
        $queue = $queue ?? $this->defaultQueue;
        $driver = $this->getDriver($driver);

        $job->setQueue($queue);
        $driver->push($queue, $job, $delay);
    }

    /**
     * Pop job from queue
     * 
     * @param string|null $queue Queue name (null = default)
     * @param string|null $driver Driver name (null = default)
     * @return JobInterface|null
     */
    public function pop(?string $queue = null, ?string $driver = null): ?JobInterface
    {
        $queue = $queue ?? $this->defaultQueue;
        $driver = $this->getDriver($driver);

        return $driver->pop($queue);
    }

    /**
     * Get queue size
     * 
     * @param string|null $queue Queue name (null = default)
     * @param string|null $driver Driver name (null = default)
     * @return int
     */
    public function size(?string $queue = null, ?string $driver = null): int
    {
        $queue = $queue ?? $this->defaultQueue;
        $driver = $this->getDriver($driver);

        return $driver->size($queue);
    }

    /**
     * Clear queue
     * 
     * @param string|null $queue Queue name (null = default)
     * @param string|null $driver Driver name (null = default)
     * @return void
     */
    public function clear(?string $queue = null, ?string $driver = null): void
    {
        $queue = $queue ?? $this->defaultQueue;
        $driver = $this->getDriver($driver);

        $driver->clear($queue);
    }
}
