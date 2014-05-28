<?php

$output['news'] = Check2::except()->post('newsId')->isIn('news', 'row');
$output['success'] = true;
