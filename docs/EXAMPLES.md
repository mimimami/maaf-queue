# MAAF Queue Példák

## Alapvető Használat

### Queue Manager Létrehozása

```php
use MAAF\Queue\QueueManager;
use MAAF\Queue\Drivers\RedisQueueDriver;
use MAAF\Queue\Drivers\DatabaseQueueDriver;

// Create queue manager
$queueManager = new QueueManager();

// Register Redis driver
$redis = new \Redis();
$redis->connect('127.0.0.1', 6379);
$queueManager->registerDriver('redis', new RedisQueueDriver($redis));

// Or register Database driver
$pdo = new PDO('sqlite:queue.db');
$queueManager->registerDriver('database', new DatabaseQueueDriver($pdo));

// Set default driver
$queueManager->setDefaultDriver('redis');
$queueManager->setDefaultQueue('default');
```

## Job Létrehozása és Dispatch

### Job Osztály

```php
use MAAF\Queue\Job;

class SendEmailJob extends Job
{
    public function handle(): void
    {
        $to = $this->data['to'];
        $subject = $this->data['subject'];
        $body = $this->data['body'];

        // Send email
        mail($to, $subject, $body);
    }

    public function fail(\Throwable $exception): void
    {
        // Log failure
        error_log("Email job failed: " . $exception->getMessage());
    }
}

class ProcessOrderJob extends Job
{
    protected int $maxAttempts = 5;
    protected int $timeout = 120;

    public function handle(): void
    {
        $orderId = $this->data['order_id'];

        // Process order
        // ... complex logic
    }
}
```

### Job Dispatch

```php
// Simple job dispatch
$job = new SendEmailJob([
    'to' => 'user@example.com',
    'subject' => 'Welcome',
    'body' => 'Welcome to our service!',
]);

$queueManager->push($job);

// Dispatch to specific queue
$queueManager->push($job, queue: 'emails');

// Dispatch with delay (5 minutes)
$queueManager->push($job, queue: 'emails', delay: 300);

// Dispatch to specific driver
$queueManager->push($job, queue: 'emails', driver: 'database');
```

## Modulonkénti Worker Konfiguráció

### Worker Konfiguráció Regisztráció

```php
use MAAF\Queue\Worker\WorkerConfiguration;
use MAAF\Queue\Worker\ModuleWorkerManager;

$workerManager = new ModuleWorkerManager($queueManager);

// UserModule worker configuration
$userConfig = new WorkerConfiguration(
    moduleName: 'UserModule',
    queue: 'user-jobs',
    processes: 2,
    timeout: 60,
    memory: 128,
    sleep: 3,
    maxTries: 3
);
$workerManager->register($userConfig);

// ProductModule worker configuration
$productConfig = new WorkerConfiguration(
    moduleName: 'ProductModule',
    queue: 'product-jobs',
    processes: 4,
    timeout: 120,
    memory: 256,
    sleep: 5,
    maxTries: 5
);
$workerManager->register($productConfig);

// Start worker for specific module
$workerManager->startWorker('UserModule');

// Start all workers
$workerManager->startAllWorkers();
```

### Worker Konfiguráció Beállítások

```php
$config = new WorkerConfiguration(
    moduleName: 'MyModule',
    queue: 'my-queue',
    processes: 2,        // Number of worker processes
    timeout: 60,         // Job timeout in seconds
    memory: 128,         // Memory limit in MB
    sleep: 3,           // Sleep time between jobs in seconds
    maxTries: 3,        // Maximum retry attempts
    force: false        // Force restart
);
```

## Queue Worker

### Worker Futtatás

```php
use MAAF\Queue\Worker\QueueWorker;

$worker = new QueueWorker($queueManager, $config);

// Start worker (blocks until stopped)
$worker->start();

// Stop worker
$worker->stop();
```

### Worker Folyamat

```php
// Worker automatikusan:
// 1. Pops job from queue
// 2. Processes job
// 3. Handles exceptions
// 4. Retries failed jobs
// 5. Sleeps between jobs
// 6. Checks memory/timeout limits
```

## Dashboard

### Dashboard Használat

```php
use MAAF\Queue\Dashboard\Dashboard;

$dashboard = new Dashboard($queueManager);

// Get statistics
$stats = $dashboard->getStatistics();

// Queue statistics
$queueStats = $stats['queues'];
// [
//     'default' => [
//         'size' => 10,
//         'processed' => 100,
//         'failed' => 2,
//     ],
// ]

// Worker statistics
$workerStats = $stats['workers'];
// [
//     'active' => 2,
//     'idle' => 1,
//     'total' => 3,
// ]

// Job statistics
$jobStats = $stats['jobs'];
// [
//     'pending' => 10,
//     'processing' => 2,
//     'completed' => 100,
//     'failed' => 2,
// ]

// Render dashboard HTML
$html = $dashboard->render();
file_put_contents('queue-dashboard.html', $html);
```

## Teljes Példa

### Setup

```php
use MAAF\Queue\QueueManager;
use MAAF\Queue\Drivers\RedisQueueDriver;
use MAAF\Queue\Worker\WorkerConfiguration;
use MAAF\Queue\Worker\ModuleWorkerManager;

// 1. Create queue manager
$queueManager = new QueueManager();

// 2. Register Redis driver
$redis = new \Redis();
$redis->connect('127.0.0.1', 6379);
$queueManager->registerDriver('redis', new RedisQueueDriver($redis));
$queueManager->setDefaultDriver('redis');

// 3. Register worker configurations
$workerManager = new ModuleWorkerManager($queueManager);

$userConfig = new WorkerConfiguration(
    moduleName: 'UserModule',
    queue: 'user-jobs',
    processes: 2,
    timeout: 60,
    memory: 128,
    sleep: 3,
    maxTries: 3
);
$workerManager->register($userConfig);

// 4. Dispatch jobs
$job = new SendEmailJob([
    'to' => 'user@example.com',
    'subject' => 'Hello',
    'body' => 'Hello World',
]);
$queueManager->push($job, queue: 'user-jobs');

// 5. Start workers
$workerManager->startWorker('UserModule');
```

### CLI Használat

```bash
# Start worker for module
php maaf queue:work UserModule

# Start all workers
php maaf queue:listen

# Generate dashboard
php maaf queue:dashboard

# Generate dashboard to specific file
php maaf queue:dashboard my-dashboard.html
```

## Job Retry és Failed Handling

### Retry Logic

```php
class MyJob extends Job
{
    protected int $maxAttempts = 3;

    public function handle(): void
    {
        // Job logic
        // If exception thrown, job will be retried
        // After maxAttempts, job will be marked as failed
    }

    public function fail(\Throwable $exception): void
    {
        // Called when job fails after max attempts
        // Log, notify, etc.
        error_log("Job failed: " . $exception->getMessage());
    }
}
```

### Manual Retry

```php
// Job is automatically retried on exception
// Retry happens after delay (configurable)
// After max attempts, job is marked as failed
```

## Queue Kezelés

### Queue Műveletek

```php
// Get queue size
$size = $queueManager->size('emails');

// Clear queue
$queueManager->clear('emails');

// Pop job manually
$job = $queueManager->pop('emails');
if ($job !== null) {
    $job->handle();
}
```

## Best Practices

### Job Design

```php
// 1. Keep jobs focused and small
class SendEmailJob extends Job
{
    public function handle(): void
    {
        // Only send email, don't do complex logic
    }
}

// 2. Use appropriate timeouts
class ProcessOrderJob extends Job
{
    protected int $timeout = 120; // 2 minutes for complex processing
}

// 3. Handle failures gracefully
class MyJob extends Job
{
    public function fail(\Throwable $exception): void
    {
        // Log, notify admin, etc.
    }
}
```

### Worker Configuration

```php
// 1. Set appropriate memory limits
$config = new WorkerConfiguration(
    moduleName: 'MyModule',
    queue: 'my-queue',
    memory: 256, // MB
);

// 2. Set appropriate timeouts
$config = new WorkerConfiguration(
    moduleName: 'MyModule',
    queue: 'my-queue',
    timeout: 60, // seconds
);

// 3. Configure retry attempts
$config = new WorkerConfiguration(
    moduleName: 'MyModule',
    queue: 'my-queue',
    maxTries: 3,
);
```
