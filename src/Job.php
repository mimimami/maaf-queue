<?php

declare(strict_types=1);

namespace MAAF\Queue;

/**
 * Job
 * 
 * Alap job osztály.
 * 
 * @version 1.0.0
 */
abstract class Job implements JobInterface
{
    protected string $id;
    protected string $queue = 'default';
    protected int $attempts = 0;
    protected int $maxAttempts = 3;
    protected int $timeout = 60;

    public function __construct(
        protected array $data = []
    ) {
        $this->id = uniqid('job_', true);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getClass(): string
    {
        return static::class;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getQueue(): string
    {
        return $this->queue;
    }

    public function setQueue(string $queue): void
    {
        $this->queue = $queue;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function incrementAttempts(): void
    {
        $this->attempts++;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function fail(\Throwable $exception): void
    {
        // Override in subclass if needed
    }

    public function serialize(): string
    {
        return serialize([
            'id' => $this->id,
            'class' => $this->getClass(),
            'data' => $this->data,
            'queue' => $this->queue,
            'attempts' => $this->attempts,
            'maxAttempts' => $this->maxAttempts,
            'timeout' => $this->timeout,
        ]);
    }

    public static function fromSerialized(string $data): JobInterface
    {
        $unserialized = unserialize($data);
        
        if (!isset($unserialized['class'])) {
            throw new \RuntimeException('Invalid job data');
        }

        $class = $unserialized['class'];
        if (!class_exists($class)) {
            throw new \RuntimeException("Job class not found: {$class}");
        }

        $job = new $class($unserialized['data'] ?? []);
        $job->id = $unserialized['id'] ?? uniqid('job_', true);
        $job->queue = $unserialized['queue'] ?? 'default';
        $job->attempts = $unserialized['attempts'] ?? 0;
        $job->maxAttempts = $unserialized['maxAttempts'] ?? 3;
        $job->timeout = $unserialized['timeout'] ?? 60;

        return $job;
    }
}
