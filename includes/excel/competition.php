<?php

$chr68 = 68;
$chr70 = 70;

$id = Check2::except()->get('id')->isIn('competitions');
$competition = FSS::competition($id);
$calculation = CalculationCompetition::build($competition);
$disciplines = $calculation->disciplines();

$excelFile = new PHPExcel();
$excelFile->getProperties()->setCreator("Feuerwehrsport - Statistik")
                             ->setTitle($competition['event'].' - '.$competition['place'].' - '.gdate($competition['date']))
                             ->setSubject("Statistik")
                             ->setDescription("Stand vom ".date('d.m.Y'))
                             ->setKeywords("Statistik, ".$competition['event'].', '.$competition['place'].', '.gdate($competition['date']));
$worksheetCount = 0;
$worksheet = $excelFile->getActiveSheet();
$worksheet->setTitle('Übersicht');

foreach ($disciplines as $discipline) {
  if (!$calculation->count($discipline)) continue;
  $worksheetCount++;

  $worksheet = $excelFile->createSheet($worksheetCount);

  $worksheet->setTitle($calculation->disciplineName($discipline, true));
  $worksheet->mergeCells('A1:E1');
  $worksheet->setCellValue('A1', $calculation->disciplineName($discipline));
  $worksheet->setBold('A1');
  $worksheet->setTextCenter('A1');


  $scores = $calculation->scores($discipline);
  $fullKey = $discipline['fullKey'];
  $key = $discipline['key'];
  $final = $discipline['final'];
  $sex = $discipline['sex'];

  if (in_array($discipline['key'], array('hb', 'hl', 'zk'))) {
    $worksheet->setTh('A3', 'Platz');
    $worksheet->setTh('B3', 'Name');
    $worksheet->setTh('C3', 'Vorname');
    if ($key == 'zk') {
      $worksheet->setTh('D3', 'HB');
      $worksheet->setTh('E3', 'HL');
      $worksheet->setTh('F3', 'Zeit');
    } else {
      $worksheet->setTh('D3', 'Mannschaft');
      $worksheet->setTh('E3', 'Zeit');
    }

    if (in_array($discipline['key'], array('hb', 'hl'))) {
      $moreScoresCount = 0;
      $scores = $calculation->scores($discipline);
      for ($line = 0; $line < count($scores); $line++) {
        $score = $scores[$line];

        // search for more times
        $times = $db->getRows("
          SELECT COALESCE(`s`.`time`, ".FSS::INVALID.") AS `time`
          FROM `scores` `s`
          INNER JOIN `persons` `p` ON `s`.`person_id` = `p`.`id`
          WHERE `discipline` = '".$key."'
          AND `s`.`id` != '".$score['id']."'
          AND `s`.`person_id` = ".$score['person_id']."
          AND `team_number` ".($final? "=" : ">")." -2
          AND `competition_id` = '".$id."'
          ".($sex? " AND `sex` = '".$sex."' " : "")."
        ", 'time');
        sort($times);
        $scores[$line]['times'] = $times;
        $moreScoresCount = max($moreScoresCount, count($times));
      }
      usort($scores, function($a, $b) {
        if ($a['time'] < $b['time']) return -1;
        if ($a['time'] > $b['time']) return 1;
        for ($i = 0; $i < min(count($a['times']), count($b['times'])); $i++) {
          if ($a['times'][$i] < $b['times'][$i]) return -1;
          if ($a['times'][$i] > $b['times'][$i]) return 1;
        }
        return 0;
      });

      for ($i = 0; $i < $moreScoresCount; $i++) {
        $worksheet->setTh(chr($chr70+$i).'3', 'Zeit '.($i+2));
        $calculatedWidth = $worksheet->getColumnDimension(chr($chr70+$i))->getWidth();
        $worksheet->getColumnDimension(chr($chr70+$i))->setWidth((int) $calculatedWidth * 1.05);
      }
    }

    $place = 0;
    foreach ($scores as $score) {
      $place++;
      $tr = $place + 4;
      $worksheet->setCellValueExplicit('A'.$tr, $place.'.');
      $worksheet->setTextCenter('A'.$tr);
      $worksheet->setBorder('A'.$tr);

      $worksheet->setCellValue('B'.$tr, $score['name']);
      $worksheet->setBorder('B'.$tr);

      $worksheet->setCellValue('C'.$tr, $score['firstname']);
      $worksheet->setBorder('C'.$tr);
 
      if ($key == 'zk') {
        $worksheet->setTime('D'.$tr, $score['hb']);
        $worksheet->setBorder('D'.$tr);
        $worksheet->setTime('E'.$tr, $score['hl']);
        $worksheet->setBorder('E'.$tr);
        $worksheet->setTime('F'.$tr, $score['time']);
        $worksheet->setBorder('F'.$tr);
      } else {
        if ($final) {
          $worksheet->setCellValue('D'.$tr, $score['shortteam'].' '.FSS::teamNumber($score['team_number'], $id, $score['team_id'], 'competition'));
        } else {
          $worksheet->setCellValue('D'.$tr, $score['shortteam']);
        }
        $worksheet->setBorder('D'.$tr);
        $worksheet->setTime('E'.$tr, $score['time']);
        $worksheet->setBorder('E'.$tr);

        for ($time = 0; $time < count($score['times']); $time++) {
          $worksheet->setTime(chr($chr70 + $time).$tr, $score['times'][$time]);
          $worksheet->setBorder(chr($chr70 + $time).$tr);
        }
      }
    }

    foreach (array('A','B','C') as $letter) {
      $worksheet->getColumnDimension($letter)->setAutoSize(true);
    }

    if ($key != 'zk') {
      $calculatedWidth = (int) $worksheet->getColumnDimension('E')->getWidth() * 1.05;
      $worksheet->getColumnDimension('E')->setWidth($calculatedWidth);
      for ($time = 0; $time < $moreScoresCount; $time++) {
        $worksheet->getColumnDimension(chr($chr70 + $time))->setWidth($calculatedWidth);
      }
      $worksheet->getColumnDimension('D')->setAutoSize(true);
    } else {
      $calculatedWidth = (int) $worksheet->getColumnDimension('F')->getWidth() * 1.05;
      $worksheet->getColumnDimension('F')->setWidth($calculatedWidth);
      $worksheet->getColumnDimension('E')->setWidth($calculatedWidth);
      $worksheet->getColumnDimension('D')->setWidth($calculatedWidth);
    }

  } else {

    $worksheet->setTh('A3', 'Platz');
    $worksheet->setTh('B3', 'Mannschaft');
    $worksheet->setTh('C3', 'Zeit');

    $moreScoresCount = 0;
    $scores = $calculation->scores($discipline);
    for ($line = 0; $line < count($scores); $line++) {
      $score = $scores[$line];

      // search for more times
      $times = $db->getRows("
        SELECT COALESCE(`s`.`time`, ".FSS::INVALID.") AS `time`
        FROM `scores_".$key."` `s`
        WHERE `s`.`id` != '".$score['id']."'
        AND `team_number` = ".$score['team_number']."
        AND `team_id` = ".$score['team_id']."
        AND `competition_id` = '".$id."'
        ".($sex? " AND `sex` = '".$sex."' " : "")."
      ", 'time');
      sort($times);
      $scores[$line]['times'] = $times;
      $moreScoresCount = max($moreScoresCount, count($times));
    }
    usort($scores, function($a, $b) {
      if ($a['time'] < $b['time']) return -1;
      if ($a['time'] > $b['time']) return 1;
      for ($i=0; $i < min(count($a['times']), count($b['times'])); $i++) { 
        if ($a['times'][$i] < $b['times'][$i]) return -1;
        if ($a['times'][$i] > $b['times'][$i]) return 1;
      }
      return 0;
    });

    for ($i = 0; $i < $moreScoresCount; $i++) {
      $worksheet->setTh(chr($chr68+$i).'3', 'Zeit '.($i+2));
      $calculatedWidth = $worksheet->getColumnDimension(chr($chr68+$i))->getWidth();
      $worksheet->getColumnDimension(chr($chr68+$i))->setWidth((int) $calculatedWidth * 1.05);
    }

    $place = 0;
    foreach ($scores as $score) {
      $place++;
      $tr = $place + 4;
      $worksheet->setCellValueExplicit('A'.$tr, $place.'.');
      $worksheet->setTextCenter('A'.$tr);
      $worksheet->setBorder('A'.$tr);
      $run = (array_key_exists('run', $score)) ? ' '.$score['run'] : '';
      $worksheet->setCellValue('B'.$tr, $score['shortteam'].' '.FSS::teamNumber($score['team_number'], $id, $score['team_id'], 'competition').$run);
      $worksheet->setBorder('B'.$tr);

      $worksheet->setTime('C'.$tr, $score['time']);
      $worksheet->setBorder('C'.$tr);

      for ($time = 0; $time < count($score['times']); $time++) { 
        $worksheet->setTime(chr($chr68 + $time).$tr, $score['times'][$time]);
        $worksheet->setBorder(chr($chr68 + $time).$tr);
      }
    }

    foreach (array('A','B') as $letter) {
      $worksheet->getColumnDimension($letter)->setAutoSize(true);
    }

    $calculatedWidth = (int) $worksheet->getColumnDimension('C')->getWidth() * 1.05;
    $worksheet->getColumnDimension('C')->setWidth($calculatedWidth);
    for ($time = 0; $time < $moreScoresCount; $time++) {
      $worksheet->getColumnDimension(chr($chr68 + $time))->setWidth($calculatedWidth);
    }
  }
}

$excelFile->setActiveSheetIndex(0);
$worksheet = $excelFile->getActiveSheet();

$worksheet->setCellValue('A1', $competition['event']);
$worksheet->getRowDimension('1')->setRowHeight(50);
$worksheet->mergeCells('A1:C1');
$worksheet->setBold('A1', 18);
$worksheet->setTextCenter('A1');

$worksheet->setCellValue('A2', $competition['place']);
$worksheet->getRowDimension('2')->setRowHeight(40);
$worksheet->mergeCells('A2:C2');
$worksheet->setBold('A2', 18);
$worksheet->setTextCenter('A2');

$worksheet->setCellValue('A3', gdate($competition['date']));
$worksheet->getRowDimension('3')->setRowHeight(30);
$worksheet->mergeCells('A3:C3');
$worksheet->setBold('A3', 14);
$worksheet->setTextCenter('A3');

$worksheet->setCellValue('A5', "Diese Datei enthält berechnete Ergebnisse von diesem Wettkampf. Es sind nicht die offiziellen Ergebnisse des Veranstalters. Die Daten wurden auf www.feuerwehrsport-statistik.de gesammelt. Bei Fehlern oder Anmerkungen bitte auf der Webseite melden.");
$worksheet->mergeCells('A5:C9');
$worksheet->getStyle('A5')->getAlignment()->setWrapText(true);
$worksheet->setTextCenter('A5');
$worksheet->setVerticalCenter('A5');

$worksheet->setCellValue('A10', "Die Ergebnisse sind auf einzelne Tabellen verteilt. Bitte dazu am unteren Rand die jeweiligen Tabellen auswählen.");
$worksheet->mergeCells('A10:C12');
$worksheet->getStyle('A10')->getAlignment()->setWrapText(true);
$worksheet->setTextCenter('A10');
$worksheet->setVerticalCenter('A10');

$overview = array();
if ($competition['name']) $overview[] = array('Name', $competition['name']);
if ($calculation->countSingleScores()) {
  if ($competition['score_type']) {
    $overview[] = array('Mannschaftswertung', $competition['persons'].'/'.$competition['run'].'/'.$competition['score']);
  } else {
    $overview[] = array('Mannschaftswertung', 'Keine');
  }
}
if ($competition['la']) $overview[] = array('Löschangriff', FSS::laType($competition['la']));
if ($competition['fs']) $overview[] = array('4x100m', FSS::laType($competition['fs']));
$overview[] = array();
$overview[] = array('', 'Frauen', 'Männer');
if ($calculation->count('hb', 'female') || $calculation->count('hb', 'male'))
  $overview[] = array('Hindernisbahn', $calculation->count('hb', 'female'), $calculation->count('hb', 'male'));
if ($calculation->count('hb', 'female', true) || $calculation->count('hb', 'male', true))
  $overview[] = array('Hindernisbahn Finale', $calculation->count('hb', 'male', true), $calculation->count('hb', 'male', true));
if ($calculation->count('hl'))
  $overview[] = array('Hakenleitersteigen', '', $calculation->count('hl'));
if ($calculation->count('hb', null, true))
  $overview[] = array('Hakenleitersteigen Finale', '', $calculation->count('hl', null, true));
if ($calculation->count('zk'))
  $overview[] = array('Zweikampf', '', $calculation->count('zk'));
if ($calculation->count('gs'))
  $overview[] = array('Gruppenstafette', $calculation->count('gs'), '');
if ($calculation->count('fs', 'female') || $calculation->count('fs', 'male'))
  $overview[] = array('Feuerwehrstafette', $calculation->count('fs', 'female'), $calculation->count('fs', 'male'));
if ($calculation->count('la', 'female') || $calculation->count('la', 'male'))
  $overview[] = array('Löschangriff', $calculation->count('la', 'female'), $calculation->count('la', 'male'));

$line = 14;
foreach ($overview as $row) {
  for ($i = 0; $i < count($row); $i++) $worksheet->setCellValue(chr(65 + $i).$line, $row[$i]);
  for ($i = 0; $i < 3; $i++) $worksheet->setBorder(chr(65 + $i).$line);
  if (count($row) == 2) $worksheet->mergeCells("B".$line.":C".$line);
  elseif (count($row) == 1) $worksheet->mergeCells("A".$line.":C".$line);
  $line++;
}

$worksheet->getColumnDimension('A')->setWidth(30);
$worksheet->getColumnDimension('B')->setWidth(15);
$worksheet->getColumnDimension('C')->setWidth(15);
