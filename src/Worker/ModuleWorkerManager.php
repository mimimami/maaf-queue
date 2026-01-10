<?php

declare(strict_types=1);

namespace MAAF\Queue\Worker;

use MAAF\Queue\QueueManager;

/**
 * Module Worker Manager
 * 
 * Modulonkénti worker konfiguráció kezelés.
 * 
 * @version 1.0.0
 */
final class ModuleWorkerManager
{
    /**
     * @var array<string, WorkerConfiguration>
     */
    private array $configurations = [];

    public function __construct(
        private readonly QueueManager $queueManager
    ) {
    }

    /**
     * Register worker configuration for module
     * 
     * @param WorkerConfiguration $config Configuration
     * @return void
     */
    public function register(WorkerConfiguration $config): void
    {
        $this->configurations[$config->getModuleName()] = $config;
    }

    /**
     * Get worker configuration for module
     * 
     * @param string $moduleName Module name
     * @return WorkerConfiguration|null
     */
    public function getConfiguration(string $moduleName): ?WorkerConfiguration
    {
        return $this->configurations[$moduleName] ?? null;
    }

    /**
     * Get all configurations
     * 
     * @return array<string, WorkerConfiguration>
     */
    public function getAllConfigurations(): array
    {
        return $this->configurations;
    }

    /**
     * Start worker for module
     * 
     * @param string $moduleName Module name
     * @return void
     */
    public function startWorker(string $moduleName): void
    {
        $config = $this->getConfiguration($moduleName);

        if ($config === null) {
            throw new \RuntimeException("Worker configuration not found for module: {$moduleName}");
        }

        $worker = new QueueWorker(
            $this->queueManager,
            $config
        );

        $worker->start();
    }

    /**
     * Start all workers
     * 
     * @return void
     */
    public function startAllWorkers(): void
    {
        foreach ($this->configurations as $config) {
            $this->startWorker($config->getModuleName());
        }
    }
}
