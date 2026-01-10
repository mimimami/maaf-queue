# Changelog

## [1.0.0] - 2024-01-XX

### Added

- ✅ **Queue Driver Abstraction**
  - `QueueDriverInterface` - Queue driver interface
  - `RedisQueueDriver` - Redis alapú queue driver
  - `DatabaseQueueDriver` - Adatbázis alapú queue driver
  - `QueueManager` - Queue kezelő több driver támogatással

- ✅ **Job Management**
  - `JobInterface` - Job interface
  - `Job` - Alap job osztály
  - Job serialization/unserialization
  - Job retry és failed handling

- ✅ **Module-Level Worker Configuration**
  - `WorkerConfiguration` - Worker konfiguráció modulonként
  - `ModuleWorkerManager` - Modulonkénti worker kezelés
  - `QueueWorker` - Queue worker job-ok feldolgozásához
  - Process, timeout, memory, sleep konfiguráció

- ✅ **Dashboard**
  - `Dashboard` - Horizon szerű queue dashboard
  - Queue statistics
  - Worker statistics
  - Job statistics
  - HTML dashboard template

- ✅ **CLI Commands**
  - `QueueWorkCommand` - Queue worker indítása modulonként
  - `QueueListenCommand` - Összes queue worker indítása
  - `QueueDashboardCommand` - Queue dashboard generálása

### Changed
- N/A (első kiadás)

### Fixed
- N/A (első kiadás)
