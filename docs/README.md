# MAAF Queue Dokumentáció

## Áttekintés

MAAF Queue egy queue driver absztrakció modulonkénti worker konfigurációval és Horizon szerű dashboard-dal.

## Funkciók

- ✅ **Queue Driver Absztrakció** - Több driver támogatás (Redis, Database, stb.)
- ✅ **Modulonkénti Worker Konfiguráció** - Modul szintű worker beállítások
- ✅ **Dashboard** - Horizon szerű queue dashboard
- ✅ **Job Kezelés** - Job dispatch, retry, failed jobs
- ✅ **CLI Támogatás** - Queue kezelés CLI parancsokkal

## Telepítés

```bash
composer require maaf/queue
```

## Használat

### Alapvető Használat

```php
use MAAF\Queue\QueueManager;
use MAAF\Queue\Drivers\RedisQueueDriver;

// Create queue manager
$queueManager = new QueueManager();

// Register Redis driver
$redis = new \Redis();
$redis->connect('127.0.0.1', 6379);
$queueManager->registerDriver('redis', new RedisQueueDriver($redis));

// Set default driver
$queueManager->setDefaultDriver('redis');
```

### Job Létrehozása

```php
use MAAF\Queue\Job;

class SendEmailJob extends Job
{
    public function handle(): void
    {
        // Send email logic
        mail($this->data['to'], $this->data['subject'], $this->data['body']);
    }
}

// Dispatch job
$job = new SendEmailJob([
    'to' => 'user@example.com',
    'subject' => 'Hello',
    'body' => 'Hello World',
]);

$queueManager->push($job, queue: 'emails');
```

### Modulonkénti Worker Konfiguráció

```php
use MAAF\Queue\Worker\WorkerConfiguration;
use MAAF\Queue\Worker\ModuleWorkerManager;

$workerManager = new ModuleWorkerManager($queueManager);

// Register worker configuration for module
$config = new WorkerConfiguration(
    moduleName: 'UserModule',
    queue: 'user-jobs',
    processes: 2,
    timeout: 60,
    memory: 128,
    sleep: 3,
    maxTries: 3
);

$workerManager->register($config);
```

### Dashboard

```php
use MAAF\Queue\Dashboard\Dashboard;

$dashboard = new Dashboard($queueManager);

// Get statistics
$stats = $dashboard->getStatistics();

// Render dashboard
$html = $dashboard->render();
file_put_contents('dashboard.html', $html);
```

## CLI Parancsok

```bash
# Start queue worker for module
php maaf queue:work UserModule

# Start all queue workers
php maaf queue:listen

# Generate dashboard
php maaf queue:dashboard
```

## További információk

- [API Dokumentáció](api.md)
- [Példák](examples.md)
- [Best Practices](best-practices.md)
