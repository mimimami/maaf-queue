<?php

declare(strict_types=1);

namespace MAAF\Queue;

/**
 * Queue Driver Interface
 * 
 * Queue driver interface absztrakcióhoz.
 * 
 * @version 1.0.0
 */
interface QueueDriverInterface
{
    /**
     * Push job to queue
     * 
     * @param string $queue Queue name
     * @param JobInterface $job Job instance
     * @param int $delay Delay in seconds
     * @return void
     */
    public function push(string $queue, JobInterface $job, int $delay = 0): void;

    /**
     * Pop job from queue
     * 
     * @param string $queue Queue name
     * @return JobInterface|null
     */
    public function pop(string $queue): ?JobInterface;

    /**
     * Get queue size
     * 
     * @param string $queue Queue name
     * @return int
     */
    public function size(string $queue): int;

    /**
     * Clear queue
     * 
     * @param string $queue Queue name
     * @return void
     */
    public function clear(string $queue): void;

    /**
     * Delete job
     * 
     * @param string $queue Queue name
     * @param string $jobId Job ID
     * @return void
     */
    public function delete(string $queue, string $jobId): void;
}
