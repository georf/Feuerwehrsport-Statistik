<?php
$output['person-spellings'] = $db->getRows("SELECT * FROM `persons_spelling` WHERE person_id IN (SELECT id FROM persons)");
$output['success'] = true;
