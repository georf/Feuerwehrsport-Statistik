<?php

$output['event'] = Check2::except()->post('eventId')->isIn('events', 'row');
$output['success'] = true;
