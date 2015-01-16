<?php
$cfgBackupPath  = "/root/backup";
$cfgBackupDate  = date("Y-m-d_H-i-s");
$cfgServerHost  = "127.0.0.1";
$cfgServerUser  = "root";
$cfgServerPass  = "";
$cfgBackupPurge = 7; // Max history time in days

clearstatcache();

$dir = opendir($cfgBackupPath);

while (false !== ($file = readdir($dir))) {
	if (substr($file, -7) == '.tar.gz') {
		if (!is_dir($cfgBackupPath . "/" . $file)) {
			$cache_time = filemtime($cfgBackupPath . "/" . $file);
			if (round(abs(time() - $cache_time)/60/60/24) > $cfgBackupPurge)
				unlink($cfgBackupPath . "/" . $file);
		}
	}
}

$msg[0]  = "---------------------------------------------------------------\n";
$msg[1]  = "Script init\n";
$msg[2]  = "Conecting DB... ";
$msg[3]  = "\n*** ERROR *** Unable to connect to server!";
$msg[4]  = "Backup init...\n";
$msg[5]  = "Backup OK!\n";
$msg[6]  = "*** ERROR *** Unable to backup!\n";
$msg[7]  = "Gziping file...\n";
$msg[8]  = "Purge temp files (.sql)... ";
$msg[9]  = "\n\tGzip OK!\n";
$msg[10] = "\n*** ERROR *** Unable to gzip files!";
$msg[11] = "\n*** ERROR *** Unable to purge files!";

shell_exec("clear");
echo $msg[0];
echo $msg[1];
echo $msg[0];
echo $msg[2];

$db = mysql_connect($cfgServerHost, $cfgServerUser, $cfgServerPass);

if ($db) {
	echo "\tOK!\n";
} else {
	echo $msg[3];
	$error = "echo $cfgBackupDate - $msg[3] > $cfgBackupPath/erro-$cfgBackupDate.log";
	shell_exec($error);
	exit();
}

$sql = "SHOW DATABASES";
$sts = mysql_query($sql, $db) or die (mysql_error());

echo $msg[0];
echo $msg[4];

while ($row = mysql_fetch_array($sts)) {
	if ($row[0] != 'mysql' && $row[0] != 'information_schema') {
		$tableName = $row[0];
		echo "$tableName = ";
		$cmd = "mysqldump --host=$cfgServerHost --user=$cfgServerUser --password=$cfgServerPass --databases $tableName > $cfgBackupPath/" . $tableName . "-" . $cfgBackupDate . ".sql";
		if (!shell_exec($cmd)) {
			echo $msg[5];
		} else {
			echo $msg[6];
			$error = "echo $cfgBackupDate - $msg[6] > erro-$cfgBackupDate.log";
			shell_exec($error);
		}
	}
}

$file = "mysql-" . $cfgBackupDate;

echo $msg[0];
echo $msg[7];

$cmd = "tar -cvzf $cfgBackupPath/$file.tar.gz $cfgBackupPath/*.sql";
if (shell_exec($cmd)) {
	echo $msg[9];
} else {
	echo $msg[10];
	$error = "echo $cfgBackupDate - $msg[10] > $cfgBackupPath/erro-$cfgBackupDate.log";
	shell_exec($error);
}

// apagar arquivos .sql
echo $msg[0];
echo $msg[8];

$cmd = "rm -f $cfgBackupPath/*.sql";

if (!shell_exec($cmd)) {
	echo "OK!\n";
} else {
	echo $msg[11];
	$error = "echo $cfgBackupDate - $msg[11] > $cfgBackupPath/erro-$cfgBackupDate.log";
	shell_exec($error);
	exit();
}

echo $msg[0];