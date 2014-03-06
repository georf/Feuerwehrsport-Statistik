<?php
echo Title::set('Veränderungen');
$logs = array();
foreach ($db->getRows("
    SELECT *
    FROM `logs`
    ORDER BY `inserted` DESC
    LIMIT 500;
") as $log) {
  $logs[] = Log::getByRow($log);
}

echo Bootstrap::row()->col(CountTable::build($logs)
  ->col("Zeitpunkt", function ($log) { return date('Y-m-d H:i', $log->inserted); }, 10)
  ->col("Beschreibung", function ($log) { return $log->descriptionHtml(); }, 15)
  ->col("Inhalt", function ($log) { return $log->content(); }, 45), 12);