#!/usr/bin/php
<?php (PHP_SAPI === 'cli') || exit();

// read config file
require_once(__DIR__.'/../lib/config.php');

// create mysql-config-file
$tmpName = '/tmp/backup_'.md5(time());
$i = 0;
while(is_file($tmpName.$i)) $i++;
$tmpName .= $i;

file_put_contents($tmpName, '');
chmod($tmpName, 0600);

file_put_contents($tmpName, '
[client]
user="'.$config['database']['username'].'"
password="'.$config['database']['password'].'"
');

$tables = array(
    'competitions',
    'disciplines',
    'errors',
    'events',
    'file_uploads',
    'links',
    'logs',
    'news',
    'persons',
    'places',
    'scores',
    'scores_gs',
    'scores_la',
    'scores_fs',
    'score_types',
    'teams',
);

foreach ($tables as $table) {
    shell_exec(
        'mysqldump '.
        ' --defaults-extra-file='.$tmpName.' '.
        ' --compact '.
        $config['database']['database'].' '.
        $table.
        ' > '.__DIR__.'/Daten/'.$table);
}

shell_exec('cd '.__DIR__.'/Daten/ ; git add . ');
$output = shell_exec('cd '.__DIR__.'/Daten/ ; git status ');
if (strpos($output, 'nothing to commit') === false) {
    shell_exec('cd '.__DIR__.'/Daten/ ; git commit -am "Backup '.date('d.m.Y').'" -q ');
    shell_exec('cd '.__DIR__.'/Daten/ ; git push -q ');
}

unlink($tmpName);


