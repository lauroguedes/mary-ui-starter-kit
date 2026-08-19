---
paths:
  - config/database.php
---

# Config

## Use Pdo\Mysql::ATTR_SSL_CA, never PDO::MYSQL_ATTR_SSL_CA
On PHP 8.5 the old `PDO::MYSQL_ATTR_*` constants are deprecated. The notice prints to stdout on every artisan boot, which corrupts the JSON on `php artisan boost:mcp` and makes every Boost MCP tool fail with "Invalid JSON output from tool process". Always use the `Pdo\Mysql::` class constants here. If Boost MCP tools suddenly return JSON syntax errors, run `php artisan --version` and look for stray notices before debugging the MCP server.
