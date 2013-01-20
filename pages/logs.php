<h1>Logs</h1>


<table class="table" style="width:99%;">
<?php

$logs = $db->getRows("
    SELECT *
    FROM `logs`
    ORDER BY `inserted` DESC
    LIMIT 500;
");

foreach($logs as $log) {
    $log = Log::getByRow($log);

    echo '<tr style="border-top:22px solid #E5E5E5;">';
    echo '<td>'.$log->datetime().'</td>';
    echo '<td>'.$log->description().'</td>';
    echo '<td>'.$log->content().'</td>';

}



?>
</table>
