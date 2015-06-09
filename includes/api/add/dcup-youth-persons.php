<?php

Check2::except()->isAdmin();
$competitionId = Check2::except()->post('competitionId')->isIn('competitions');
$dcupId = Check2::except()->post('dcupId')->isIn('dcups');
$personIds = explode(",", Check2::except()->post('personIds')->present());

$scores = DcupCalculation::singleYouth($competitionId, $personIds, 'HB', 'female');
$scores = array_merge($scores, DcupCalculation::singleYouth($competitionId, $personIds, 'HL', 'female'));
$scores = array_merge($scores, DcupCalculation::singleYouth($competitionId, $personIds, 'HB', 'male'));
$scores = array_merge($scores, DcupCalculation::singleYouth($competitionId, $personIds, 'HL', 'male'));
DcupCalculation::insertSingle($scores, $dcupId, true);
DcupCalculation::zkYouth($competitionId, $dcupId, $personIds);
DcupCalculation::calculate(true);

$output["scores"] = $scores;
$output['success'] = true;
