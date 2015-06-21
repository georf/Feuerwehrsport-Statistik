<?php
$output['people'] = $db->getRows("SELECT * FROM `persons`");
$output['success'] = true;
