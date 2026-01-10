<?php

declare(strict_types=1);

namespace MAAF\Queue;

/**
 * Job Interface
 * 
 * Job interface queue job-okhoz.
 * 
 * @version 1.0.0
 */
interface JobInterface
{
    /**
     * Get job ID
     * 
     * @return string
     */
    public function getId(): string;

    /**
     * Get job class
     * 
     * @return string
     */
    public function getClass(): string;

    /**
     * Get job data
     * 
     * @return array<string, mixed>
     */
    public function getData(): array;

    /**
     * Get queue name
     * 
     * @return string
     */
    public function getQueue(): string;

    /**
     * Get attempts
     * 
     * @return int
     */
    public function getAttempts(): int;

    /**
     * Increment attempts
     * 
     * @return void
     */
    public function incrementAttempts(): void;

    /**
     * Handle job
     * 
     * @return void
     */
    public function handle(): void;

    /**
     * Mark job as failed
     * 
     * @param \Throwable $exception Exception
     * @return void
     */
    public function fail(\Throwable $exception): void;

    /**
     * Serialize job
     * 
     * @return string
     */
    public function serialize(): string;

    /**
     * Unserialize job from data
     * 
     * @param string $data Serialized data
     * @return JobInterface
     */
    public static function fromSerialized(string $data): JobInterface;
}
