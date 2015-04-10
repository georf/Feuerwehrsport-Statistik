<?php

Check2::except()->isSubAdmin();
$team = Check2::except()->post('teamId')->isIn('teams', 'row');
$attachedFile = Check2::except()->post('attachedFile')->present();

TeamLogo::build($team)->remove();

$basename = preg_replace('|\.[^.]+$|', '', $attachedFile);
$newName = $basename.'.png';
$logoPath = $config['base'].$config['logo-path'];

$n = 1;
while (is_file($logoPath.$n.$newName)) $n++;

$db->updateRow('teams', $team['id'], array(
  'logo' => $n.$newName
));

if ($attachedFile != $newName) {
  shell_exec('convert '.$config['error-file-path'].$attachedFile.' '.$logoPath.$n.$newName);
} else {
  rename($config['error-file-path'].$attachedFile, $logoPath.$n.$newName);
}
shell_exec('mogrify -resize 100x100 -background transparent -gravity center -extent 100x100 -format png '.$logoPath.$n.$newName);
Log::insertWithAlert('add-logo', array('team_id' => $team['id']));

$output['success'] = true;