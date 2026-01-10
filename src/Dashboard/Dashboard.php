<?php

declare(strict_types=1);

namespace MAAF\Queue\Dashboard;

use MAAF\Queue\QueueManager;

/**
 * Dashboard
 * 
 * Horizon szerű queue dashboard.
 * 
 * @version 1.0.0
 */
final class Dashboard
{
    public function __construct(
        private readonly QueueManager $queueManager
    ) {
    }

    /**
     * Get queue statistics
     * 
     * @return array<string, mixed>
     */
    public function getStatistics(): array
    {
        return [
            'queues' => $this->getQueueStats(),
            'workers' => $this->getWorkerStats(),
            'jobs' => $this->getJobStats(),
        ];
    }

    /**
     * Get queue statistics
     * 
     * @return array<string, array<string, mixed>>
     */
    public function getQueueStats(): array
    {
        // This would typically query from a storage/database
        // For now, return basic structure
        return [
            'default' => [
                'size' => $this->queueManager->size('default'),
                'processed' => 0,
                'failed' => 0,
            ],
        ];
    }

    /**
     * Get worker statistics
     * 
     * @return array<string, mixed>
     */
    public function getWorkerStats(): array
    {
        // This would typically query from a storage/database
        return [
            'active' => 0,
            'idle' => 0,
            'total' => 0,
        ];
    }

    /**
     * Get job statistics
     * 
     * @return array<string, mixed>
     */
    public function getJobStats(): array
    {
        // This would typically query from a storage/database
        return [
            'pending' => 0,
            'processing' => 0,
            'completed' => 0,
            'failed' => 0,
        ];
    }

    /**
     * Render dashboard HTML
     * 
     * @return string
     */
    public function render(): string
    {
        $stats = $this->getStatistics();

        ob_start();
        include __DIR__ . '/templates/dashboard.php';
        return ob_get_clean();
    }
}
