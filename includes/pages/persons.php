<?php
TempDB::generate('x_scores_hbf');
TempDB::generate('x_scores_hbm');
TempDB::generate('x_scores_hlf');
TempDB::generate('x_scores_hlm');

echo Bootstrap::row()
  ->col(Title::set('Wettkämpfer'), 9)
  ->col(TableOfContents::get()
    ->link('female', 'Weiblich')
    ->link('male', 'Männlich')
    ->link('personhinzufuegen', 'Person hinzufügen')
  , 3);

$sexs = array(
  'female' => 'Weiblich',
  'male' => 'Männlich',
);

foreach ($sexs as $sex => $title) {
  echo Title::h2($title, $sex);
  $persons = $db->getRows("
    SELECT `p`.`id`, `p`.`name`, `p`.`firstname`,`n`.`short` AS `nation`,
    (
      SELECT COUNT(`id`) AS `count`
      FROM `x_scores_hb".substr($sex,0,1)."`
      WHERE `person_id` = `p`.`id`
    ) AS `hb`,
    (
      SELECT COUNT(`pp`.`id`) AS `count`
      FROM `person_participations` `pp`
      INNER JOIN `group_scores` `gs` ON `pp`.`score_id` = `gs`.`id`
      INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
      INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
      WHERE `person_id` = `p`.`id`
      AND `gst`.`discipline` = 'LA'
    ) AS `la`,
    (
      SELECT COUNT(`pp`.`id`) AS `count`
      FROM `person_participations` `pp`
      INNER JOIN `group_scores` `gs` ON `pp`.`score_id` = `gs`.`id`
      INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
      INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
      WHERE `person_id` = `p`.`id`
      AND `gst`.`discipline` = 'FS'
    ) AS `fs`,
    (
      SELECT COUNT(`id`) AS `count`
      FROM `x_scores_hl".substr($sex,0,1)."`
      WHERE `person_id` = `p`.`id`
    ) AS `hl`
    ".(($sex === 'female')?",
    (
      SELECT COUNT(`pp`.`id`) AS `count`
      FROM `person_participations` `pp`
      INNER JOIN `group_scores` `gs` ON `pp`.`score_id` = `gs`.`id`
      INNER JOIN `group_score_categories` `gsc` ON `gs`.`group_score_category_id` = `gsc`.`id`
      INNER JOIN `group_score_types` `gst` ON `gsc`.`group_score_type_id` = `gst`.`id`
      WHERE `person_id` = `p`.`id`
      AND `gst`.`discipline` = 'GS'
    ) AS `gs`
    ":"")."
    FROM `persons` `p`
    LEFT JOIN `nations` `n` ON `p`.`nation_id` = `n`.`id`
    WHERE `sex` = '".$sex."'
  ");
  $table = CountTable::build($persons, array('datatable-'.$sex))
    ->col('Name', 'name', 15)
    ->col('Vorname', 'firstname', 15)
    ->col('HB', function ($row) { return FSS::countNoEmpty($row['hb']); }, 5) 
    ->col('HL', function ($row) { return FSS::countNoEmpty($row['hl']); }, 5)
    ->col('LA', function ($row) { return FSS::countNoEmpty($row['la']); }, 5)
    ->col('FS', function ($row) { return FSS::countNoEmpty($row['fs']); }, 5);
  if ($sex == 'female') {
    $table->col('GS', function ($row) { return FSS::countNoEmpty($row['gs']); }, 5);
  }

  $table
    ->col('Nation', function ($row) { return $row['nation']; }, 5)
    ->col('', function ($row) { return Link::person($row['id'], 'Details', $row['name'], $row['firstname']); }, 7);
  echo Bootstrap::row()->col($table, 12);
}

echo Title::h2('Neue Person hinzufügen', 'personhinzufuegen');
echo Bootstrap::row()->col(
  '<p class="six columns">Ist eine Person die du kennst noch nicht eingetragen? Dann trage sie doch schnell ins System ein!</p>'.
  '<p><button id="add-person">Person hinzufügen</button></p>'
  , 5)
  ->col('<img src="/styling/images/system-users.png" alt=""/>', 3);
