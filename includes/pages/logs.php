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


$table = CountTable::build($logs)
  ->col("Zeitpunkt", function ($log) { return date('Y-m-d H:i', $log->inserted); }, 10)
  ->col("Beschreibung", function ($log) { return $log->descriptionHtml(); }, 15);

if (isset($adminLogs)) {
  $table
  ->col("Inhalt", function ($log) { return $log->content()."<br/><pre>".json_encode($log->raw, JSON_PRETTY_PRINT)."</pre>"; }, 45)
  ->col("Benutzer", function ($log) { 
    return Login::getNameLink($log->userId).Login::getMailLink($log->userId); 
  }, 10);
} else {
  $table->col("Inhalt", function ($log) { return $log->content(); }, 45);
}

echo Bootstrap::row()->col($table, 12);