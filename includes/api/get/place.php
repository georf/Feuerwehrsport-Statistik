<?php

$output['place'] = Check2::except()->post('placeId')->isIn('places', 'row');
$output['success'] = true;
