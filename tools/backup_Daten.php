#!/usr/bin/php
<?php (PHP_SAPI === 'cli') || exit();

// read config file
require_once(__DIR__.'/../includes/lib/config.php');

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
  'competition_hints',
  'dates',
  'dcup_points',
  'dcup_points_u',
  'dcups',
  'errors',
  'events',
  'group_scores',
  'group_score_categories',
  'group_score_types',
  'links',
  'logs',
  'nations',
  'news',
  'person_participations',
  'persons',
  'persons_spelling',
  'places',
  'result_files',
  'score_types',
  'scores',
  'scores_dcup_single',
  'scores_dcup_single_u',
  'scores_dcup_team',
  'scores_dcup_zk',
  'scores_dcup_zk_u',
  'teams',
  'teams_spelling',
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

shell_exec('cd '.__DIR__.'/Daten/ ; git add -A . ');
$output = shell_exec('cd '.__DIR__.'/Daten/ ; git status ');
if (strpos($output, 'nothing to commit') === false) {
    shell_exec('cd '.__DIR__.'/Daten/ ; git commit -am "Backup '.date('d.m.Y').'" -q ');
    shell_exec('cd '.__DIR__.'/Daten/ ; git push -q ');
}

unlink($tmpName);


