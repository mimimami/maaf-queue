<?php

declare(strict_types=1);

namespace MAAF\Queue\CLI\Commands;

use MAAF\Core\Cli\CommandInterface;
use MAAF\Queue\Worker\ModuleWorkerManager;

/**
 * Queue Work Command
 * 
 * Queue worker indítása.
 * 
 * @version 1.0.0
 */
final class QueueWorkCommand implements CommandInterface
{
    public function __construct(
        private readonly ?ModuleWorkerManager $workerManager = null
    ) {
    }

    public function getName(): string
    {
        return 'queue:work';
    }

    public function getDescription(): string
    {
        return 'Start queue worker';
    }

    public function execute(array $args): int
    {
        if ($this->workerManager === null) {
            echo "❌ Worker manager not available\n";
            return 1;
        }

        $moduleName = $args[0] ?? null;

        if ($moduleName === null) {
            echo "❌ Module name required\n";
            echo "Usage: php maaf queue:work <module-name>\n";
            return 1;
        }

        echo "Starting queue worker for module: {$moduleName}\n";
        $this->workerManager->startWorker($moduleName);

        return 0;
    }
}
