<?php

declare(strict_types=1);

namespace MAAF\Queue\CLI\Commands;

use MAAF\Core\Cli\CommandInterface;
use MAAF\Queue\Worker\ModuleWorkerManager;

/**
 * Queue Listen Command
 * 
 * Összes queue worker indítása.
 * 
 * @version 1.0.0
 */
final class QueueListenCommand implements CommandInterface
{
    public function __construct(
        private readonly ?ModuleWorkerManager $workerManager = null
    ) {
    }

    public function getName(): string
    {
        return 'queue:listen';
    }

    public function getDescription(): string
    {
        return 'Start all queue workers';
    }

    public function execute(array $args): int
    {
        if ($this->workerManager === null) {
            echo "❌ Worker manager not available\n";
            return 1;
        }

        echo "Starting all queue workers...\n";
        $this->workerManager->startAllWorkers();

        return 0;
    }
}
