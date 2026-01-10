<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAAF Queue Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-card h3 { font-size: 14px; color: #666; margin-bottom: 10px; }
        .stat-card .value { font-size: 32px; font-weight: bold; color: #333; }
        .queues { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .queues h2 { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9f9f9; font-weight: 600; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>MAAF Queue Dashboard</h1>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3>Pending Jobs</h3>
                <div class="value"><?= $stats['jobs']['pending'] ?? 0 ?></div>
            </div>
            <div class="stat-card">
                <h3>Processing</h3>
                <div class="value"><?= $stats['jobs']['processing'] ?? 0 ?></div>
            </div>
            <div class="stat-card">
                <h3>Completed</h3>
                <div class="value"><?= $stats['jobs']['completed'] ?? 0 ?></div>
            </div>
            <div class="stat-card">
                <h3>Failed</h3>
                <div class="value"><?= $stats['jobs']['failed'] ?? 0 ?></div>
            </div>
        </div>

        <div class="queues">
            <h2>Queues</h2>
            <table>
                <thead>
                    <tr>
                        <th>Queue</th>
                        <th>Size</th>
                        <th>Processed</th>
                        <th>Failed</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['queues'] as $queueName => $queueStats): ?>
                    <tr>
                        <td><?= htmlspecialchars($queueName) ?></td>
                        <td><?= $queueStats['size'] ?? 0 ?></td>
                        <td><?= $queueStats['processed'] ?? 0 ?></td>
                        <td><?= $queueStats['failed'] ?? 0 ?></td>
                        <td>
                            <?php if (($queueStats['size'] ?? 0) > 0): ?>
                                <span class="badge badge-warning">Active</span>
                            <?php else: ?>
                                <span class="badge badge-success">Idle</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
