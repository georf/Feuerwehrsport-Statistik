<?php

$output['types'] = $db->getRows("
    SELECT *
    FROM `score_types`
");
