# MAAF Queue

Queue driver absztrakció modulonkénti worker konfigurációval és Horizon szerű dashboard-dal.

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

Lásd a [dokumentációt](docs/README.md) részletes információkért.

## Licenc

MIT License
