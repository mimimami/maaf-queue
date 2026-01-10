<?php

declare(strict_types=1);

namespace MAAF\Queue\Drivers;

use MAAF\Queue\JobInterface;
use MAAF\Queue\QueueDriverInterface;

/**
 * Redis Queue Driver
 * 
 * Redis alapú queue driver.
 * 
 * @version 1.0.0
 */
final class RedisQueueDriver implements QueueDriverInterface
{
    /**
     * @var \Redis|\Predis\Client
     */
    private $redis;

    public function __construct($redis)
    {
        $this->redis = $redis;
    }

    public function push(string $queue, JobInterface $job, int $delay = 0): void
    {
        $queueKey = $this->getQueueKey($queue);
        $serialized = $job->serialize();

        if ($delay > 0) {
            // Use sorted set for delayed jobs
            $this->redis->zadd($queueKey . ':delayed', time() + $delay, $serialized);
        } else {
            // Use list for immediate jobs
            $this->redis->rpush($queueKey, $serialized);
        }
    }

    public function pop(string $queue): ?JobInterface
    {
        $queueKey = $this->getQueueKey($queue);

        // Check delayed jobs
        $delayed = $this->redis->zrangebyscore($queueKey . ':delayed', 0, time(), ['limit' => [0, 1]]);
        if (!empty($delayed)) {
            $jobData = $delayed[0];
            $this->redis->zrem($queueKey . ':delayed', $jobData);
            return $this->unserializeJob($jobData);
        }

        // Pop from immediate queue
        $jobData = $this->redis->lpop($queueKey);
        if ($jobData === null || $jobData === false) {
            return null;
        }

        return $this->unserializeJob($jobData);
    }

    public function size(string $queue): int
    {
        $queueKey = $this->getQueueKey($queue);
        return (int)$this->redis->llen($queueKey);
    }

    public function clear(string $queue): void
    {
        $queueKey = $this->getQueueKey($queue);
        $this->redis->del($queueKey);
        $this->redis->del($queueKey . ':delayed');
    }

    public function delete(string $queue, string $jobId): void
    {
        // Note: This is a simplified implementation
        // In production, you'd need to track job IDs
        $queueKey = $this->getQueueKey($queue);
        // Remove from immediate queue (would need to iterate)
        // Remove from delayed queue
        $this->redis->zremrangebyscore($queueKey . ':delayed', 0, '+inf');
    }

    /**
     * Get queue key
     * 
     * @param string $queue Queue name
     * @return string
     */
    private function getQueueKey(string $queue): string
    {
        return "queue:{$queue}";
    }

    /**
     * Unserialize job
     * 
     * @param string $data Serialized data
     * @return JobInterface
     */
    private function unserializeJob(string $data): JobInterface
    {
        $unserialized = unserialize($data);
        
        if (!isset($unserialized['class'])) {
            throw new \RuntimeException('Invalid job data');
        }

        $class = $unserialized['class'];
        if (!class_exists($class) || !is_subclass_of($class, \MAAF\Queue\Job::class)) {
            throw new \RuntimeException("Job class not found or invalid: {$class}");
        }

        return $class::fromSerialized($data);
    }
}
