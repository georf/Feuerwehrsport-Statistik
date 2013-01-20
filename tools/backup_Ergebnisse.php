#!/usr/bin/php
<?php (PHP_SAPI === 'cli') || exit();

$working = __DIR__.'/Ergebnisse/';

// remove old files
shell_exec('cd '.$working.' ; rm -rf files/ ');

// copy new files
shell_exec('cd '.$working.' ; cp -r ../../files/ ./ ');

// add into git
shell_exec('cd '.$working.' ; git add -A . ');

$output = shell_exec('cd '.$working.' ; git status ');
if (strpos($output, 'nothing to commit') === false) {
    shell_exec('cd '.$working.' ; git commit -am "Backup '.date('d.m.Y').'" -q ');
    shell_exec('cd '.$working.' ; git push -q ');
}



