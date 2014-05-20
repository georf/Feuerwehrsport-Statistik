<?php
TempDB::generate('x_scores_hbm');
TempDB::generate('x_scores_hbf');
TempDB::generate('x_scores_hl');

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
    SELECT `id`, `name`, `firstname`,
    (
      SELECT COUNT(`id`) AS `count`
      FROM `x_scores_hb".substr($sex,0,1)."`
      WHERE `person_id` = `p`.`id`
    ) AS `hb`,
    (
      SELECT COUNT(`id`) AS `count`
      FROM `person_participations_la` `pp` ON `pp`.`person_id` = `p`.`id`
    ) AS `la`,
    (
      SELECT COUNT(`id`) AS `count`
      FROM `person_participations_fs` `pp` ON `pp`.`person_id` = `p`.`id`
    ) AS `fs`,
    (
      SELECT COUNT(`id`) AS `count`
      ".(($sex === 'male')?"
      FROM `x_scores_hl`
      WHERE `person_id` = `p`.`id`
      ":"
      FROM `person_participations_gs` `pp` ON `pp`.`person_id` = `p`.`id`
      ")."
    ) AS `fourth`
    FROM `persons` `p`
    WHERE `sex` = '".$sex."'
  ");
  echo Bootstrap::row()->col(CountTable::build($persons)
    ->col('Name', 'name', 15)
    ->col('Vorname', 'firstname', 15)
    ->col('HB', function ($row) { return FSS::countNoEmpty($row['hb']); }, 5) 
    ->col(($sex === 'male')?'HL':'GS', function ($row) { return FSS::countNoEmpty($row['fourth']); }, 5)
    ->col('LA', function ($row) { return FSS::countNoEmpty($row['la']); }, 5)
    ->col('FS', function ($row) { return FSS::countNoEmpty($row['fs']); }, 5)
    ->col('', function ($row) { return Link::person($row['id'], 'Details', $row['name'], $row['firstname']); }, 7)
  , 12);
}

echo Title::h2('Neue Person hinzufügen', 'personhinzufuegen');
echo Bootstrap::row()->col(
  '<p class="six columns">Ist eine Person die du kennst noch nicht eingetragen? Dann trage sie doch schnell ins System ein!</p>'.
  '<p><button id="add-person">Person hinzufügen</button></p>'
  , 5)
  ->col('<img src="/styling/images/system-users.png" alt=""/>', 3);
