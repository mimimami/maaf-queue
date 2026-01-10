<?php

declare(strict_types=1);

namespace MAAF\Queue\CLI\Commands;

use MAAF\Core\Cli\CommandInterface;
use MAAF\Queue\Dashboard\Dashboard;

/**
 * Queue Dashboard Command
 * 
 * Queue dashboard megjelenítése.
 * 
 * @version 1.0.0
 */
final class QueueDashboardCommand implements CommandInterface
{
    public function __construct(
        private readonly ?Dashboard $dashboard = null
    ) {
    }

    public function getName(): string
    {
        return 'queue:dashboard';
    }

    public function getDescription(): string
    {
        return 'Show queue dashboard';
    }

    public function execute(array $args): int
    {
        if ($this->dashboard === null) {
            echo "❌ Dashboard not available\n";
            return 1;
        }

        $outputFile = $args[0] ?? 'queue-dashboard.html';

        echo "Generating dashboard...\n";
        $html = $this->dashboard->render();
        file_put_contents($outputFile, $html);

        echo "✅ Dashboard generated: {$outputFile}\n";
        return 0;
    }
}
