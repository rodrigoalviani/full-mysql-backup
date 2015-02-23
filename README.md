# Full MySQL Backup
Backup all databases on a MySQL server.

## How To Use
Change only this lines:
```php
$cfgBackupPath  = "/root/backup";
$cfgBackupDate  = date("Y-m-d_H-i-s");
$cfgServerHost  = "127.0.0.1";
$cfgServerUser  = "root";
$cfgServerPass  = "";
$cfgBackupPurge = 7; // Max history time in days
```