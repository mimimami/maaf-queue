<?php

declare(strict_types=1);

namespace MAAF\Queue\Worker;

use MAAF\Queue\JobInterface;
use MAAF\Queue\QueueManager;

/**
 * Queue Worker
 * 
 * Queue worker job-ok feldolgozásához.
 * 
 * @version 1.0.0
 */
final class QueueWorker
{
    private bool $shouldStop = false;

    public function __construct(
        private readonly QueueManager $queueManager,
        private readonly WorkerConfiguration $config
    ) {
    }

    /**
     * Start worker
     * 
     * @return void
     */
    public function start(): void
    {
        $this->shouldStop = false;

        while (!$this->shouldStop) {
            $job = $this->queueManager->pop($this->config->getQueue());

            if ($job === null) {
                sleep($this->config->getSleep());
                continue;
            }

            $this->processJob($job);
        }
    }

    /**
     * Stop worker
     * 
     * @return void
     */
    public function stop(): void
    {
        $this->shouldStop = true;
    }

    /**
     * Process job
     * 
     * @param JobInterface $job Job instance
     * @return void
     */
    private function processJob(JobInterface $job): void
    {
        try {
            $startTime = time();
            $memoryStart = memory_get_usage();

            // Check timeout
            set_time_limit($this->config->getTimeout());

            // Check memory limit
            $memoryLimit = $this->config->getMemory() * 1024 * 1024;

            // Handle job
            $job->handle();

            // Check if job exceeded limits
            $executionTime = time() - $startTime;
            $memoryUsed = memory_get_usage() - $memoryStart;

            if ($executionTime > $this->config->getTimeout()) {
                throw new \RuntimeException("Job exceeded timeout: {$executionTime}s");
            }

            if ($memoryUsed > $memoryLimit) {
                throw new \RuntimeException("Job exceeded memory limit: " . round($memoryUsed / 1024 / 1024, 2) . "MB");
            }
        } catch (\Throwable $e) {
            $job->incrementAttempts();

            if ($job->getAttempts() >= $this->config->getMaxTries()) {
                $job->fail($e);
            } else {
                // Retry job
                $this->queueManager->push($job, $this->config->getQueue(), delay: 60);
            }
        }
    }
}
