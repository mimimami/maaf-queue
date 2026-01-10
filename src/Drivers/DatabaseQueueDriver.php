<?php

declare(strict_types=1);

namespace MAAF\Queue\Drivers;

use MAAF\Queue\JobInterface;
use MAAF\Queue\QueueDriverInterface;
use PDO;

/**
 * Database Queue Driver
 * 
 * Adatbázis alapú queue driver.
 * 
 * @version 1.0.0
 */
final class DatabaseQueueDriver implements QueueDriverInterface
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly string $table = 'queue_jobs'
    ) {
        $this->createTable();
    }

    public function push(string $queue, JobInterface $job, int $delay = 0): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO {$this->table} (queue, job_id, job_class, job_data, attempts, available_at, created_at) 
             VALUES (:queue, :job_id, :job_class, :job_data, :attempts, :available_at, :created_at)"
        );

        $stmt->execute([
            'queue' => $queue,
            'job_id' => $job->getId(),
            'job_class' => $job->getClass(),
            'job_data' => $job->serialize(),
            'attempts' => 0,
            'available_at' => date('Y-m-d H:i:s', time() + $delay),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function pop(string $queue): ?JobInterface
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM {$this->table} 
                 WHERE queue = :queue AND available_at <= :now AND reserved = 0 
                 ORDER BY id ASC LIMIT 1 FOR UPDATE"
            );

            $stmt->execute([
                'queue' => $queue,
                'now' => date('Y-m-d H:i:s'),
            ]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                $this->pdo->commit();
                return null;
            }

            // Mark as reserved
            $updateStmt = $this->pdo->prepare(
                "UPDATE {$this->table} SET reserved = 1, reserved_at = :reserved_at WHERE id = :id"
            );
            $updateStmt->execute([
                'id' => $row['id'],
                'reserved_at' => date('Y-m-d H:i:s'),
            ]);

            $this->pdo->commit();

            return $this->unserializeJob($row['job_data']);
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function size(string $queue): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE queue = :queue AND reserved = 0"
        );
        $stmt->execute(['queue' => $queue]);

        return (int)$stmt->fetchColumn();
    }

    public function clear(string $queue): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE queue = :queue");
        $stmt->execute(['queue' => $queue]);
    }

    public function delete(string $queue, string $jobId): void
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM {$this->table} WHERE queue = :queue AND job_id = :job_id"
        );
        $stmt->execute([
            'queue' => $queue,
            'job_id' => $jobId,
        ]);
    }

    /**
     * Create queue table
     * 
     * @return void
     */
    private function createTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            queue VARCHAR(255) NOT NULL,
            job_id VARCHAR(255) NOT NULL,
            job_class VARCHAR(255) NOT NULL,
            job_data TEXT NOT NULL,
            attempts INTEGER DEFAULT 0,
            reserved INTEGER DEFAULT 0,
            reserved_at DATETIME NULL,
            available_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            INDEX idx_queue (queue),
            INDEX idx_available_at (available_at),
            INDEX idx_reserved (reserved)
        )";

        $this->pdo->exec($sql);
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
