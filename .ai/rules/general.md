---
paths:
  - phpunit.xml
---

# General

## Keep APP_MAINTENANCE_DRIVER=array so parallel tests work
`demo:reset` calls `Artisan::call('down')`. With the `file` driver that writes `storage/framework/maintenance.php`, which every parallel Pest process shares — concurrent HTTP tests in other processes then fail with a 503 from `PreventRequestsDuringMaintenance`. Symptom: ~8 random tests fail with 503 only under `--parallel`, never serially. Laravel 13's `array` maintenance driver keeps the flag in memory per process. Do not switch this back to `file`.
