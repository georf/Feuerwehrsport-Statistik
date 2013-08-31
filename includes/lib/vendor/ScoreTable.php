<?php

class ScoreTable {
    $scores = array();

    public function get($scores, $key) {
        $scoreTable = new self($scores, $key);
        return $scoreTable->getContent();
    }

    private function __construct($scores, $key) {
        $this->scores = $scores;
    }

    private function getContent() {
        if (count($this->scores) === 0) return '';

        $content = '';

        $name = FSS::dis2name($key);

        $sum  = 0;
        $i    = 0;
        $best = PHP_INT_MAX;
        $bad  = 0;

        foreach ($scores as $score) {
            if (FSS::isInvalid($score['time'])) continue;

            $sum += $score['time'];
            $i++;

            if ($best > $score['time']) {
                $best = $score['time'];
            }
            if ($bad < $score['time']) {
                $bad = $score['time'];
            }
        }

        $content .= '<div class="competition-box">';
        $content .= '<h2 style="clear:both; margin-top:40px;" id="dis-'.FSS::name2id($name).'">';
        $content .=   FSS::dis2img($key).' '.$name;
        $content .= '</h2>';

        $content .=  '<table class="chart-table">';

        if ($i > 0) {
            $content .= '<tr><th>Bestzeit:</th><td>',FSS::time($best),'</td></tr>';
            $content .= '<tr><th>Schlechteste Zeit:</th><td>',FSS::time($bad),'</td></tr>';
            $content .= '<tr><th>Durchschnitt:</th><td>',FSS::time($sum/$i),'</td></tr>';
        }

        $content .= '<tr><th>Zeiten:</th><td>',count($scores),'</td></tr>';

        if ($key != 'zk') {
            $content .= '<tr><td colspan="2" style="text-align:center;">'.Chart::img('person_bad_good', array($_id, $key)).'</td></tr>';
        }

        $content .= '</table>';

        if ($i > 0) $content .= '<p class="chart">'.Chart::img('person', array($_id, $key)).'</p>';

        if (in_array($key, array('hl', 'hb'))) {
            $content .= '<table class="datatable sc_'.$key.'"><thead><tr>';
            $content .=   '<th style="width:16%">Wettkampf</th>';
            $content .=   '<th style="width:25%">Ort</th>';
            $content .=   '<th style="width:31%">Mannschaft</th>';
            $content .=   '<th style="width:10%">Datum</th>';
            $content .=   '<th style="width:10%">Zeit</th>';
            $content .=   '<th style="width:8%"></th>';
            $content .= '</tr></thead><tbody>';

            foreach ($scores as $score) {

            echo
            '<tr data-id="',$score['score_id'],'">',
              '<td>'.Link::event($score['event_id'], $score['event']).'</td>',
              '<td>'.Link::place($score['place_id'], $score['place']).'</td>',
              '<td class="team">';

            if ($score['team_id']) {

                $t_name = $teams[$score['team_id']]['name'];
                if ($score['score_type_id']) {
                    $t_name .= FSS::teamNumber($score['team_number'], $score['competition_id'], $score['team_id'], false, ' ');
                }
                echo Link::team($score['team_id'], $t_name);
            }
            echo '</td>',
              '<td>',$score['date'],'</td>',
              '<td class="number">',FSS::time($score['time']),'</td>',
              '<td>'.Link::competition($score['competition_id'],'Details').'</td>',
              '</tr>';
        }
        echo '</tbody></table>';

        echo '<h3 style="clear:both">'.$name.' - Vergleich der Bestzeiten mit anderen Sportler</h3>';
        echo '<p class="chart">'.Chart::img('person_best_score', array($_id, $key)).'</p>';


    } elseif ($key == 'zk') {


        echo
          '<table class="datatable sc_'.$key.'"><thead><tr>',
            '<th style="width:16%">Wettkampf</th>',
            '<th style="width:25%">Ort</th>',
            '<th style="width:10%">Datum</th>',
            '<th style="width:12%">HB</th>',
            '<th style="width:12%">HL</th>',
            '<th style="width:12%">Zeit</th>',
            '<th style="width:8%"></th>',
          '</tr></thead><tbody>';
        foreach ($scores as $score) {

            echo
            '<tr data-scoreid="',$score['score_id'],'">',
              '<td>'.Link::event($score['event_id'], $score['event']).'</td>',
              '<td>'.Link::place($score['place_id'], $score['place']).'</td>',
              '<td>',$score['date'],'</td>',
              '<td>',FSS::time($score['hb']),'</td>',
              '<td>',FSS::time($score['hl']),'</td>',
              '<td>',FSS::time($score['time']),'</td>',
              '<td>'.Link::competition($score['competition_id'],'Details').'</td>',
              '</tr>';
        }
        echo '</tbody></table>';


    } else {


        echo '<table class="datatable sc_'.$key.'"><thead><tr>',
            '<th style="width:13%">Wettkampf</th>',
            '<th style="width:17%">Ort</th>',
            '<th style="width:28%">Mannschaft</th>',
            '<th style="width:10%">Datum</th>',
            '<th style="width:10%">Zeit</th>',
            '<th style="width:14%">Position</th>',
            '<th style="width:8%"></th>',
          '</tr></thead><tbody>';
        foreach ($scores as $score) {

            echo
            '<tr data-scoreid="',$score['score_id'],'">',
              '<td>'.Link::event($score['event_id'], $score['event']).'</td>',
              '<td>'.Link::place($score['place_id'], $score['place']).'</td>',
              '<td class="team">';

            if ($score['team_id']) {

                $t_name = $teams[$score['team_id']]['name'];
                if ($score['score_type_id']) {
                    $t_name .= FSS::teamNumber($score['team_number'], $score['competition_id'], $score['team_id'], false, ' ');
                }
                echo Link::team($score['team_id'], $t_name);
            }
            echo '</td>',
              '<td>',$score['date'],'</td>',
              '<td class="timecol">',FSS::time($score['time']),'</td>';


            for ($wk = 1; $wk < 8; $wk++) {
                if (array_key_exists('person_'.$wk, $score) && $score['person_'.$wk] == $id) {
                    echo '<td>'.WK::type($wk, $person['sex'], $key).'</td>';
                    break;
                }
            }

            echo
              '<td>'.Link::competition($score['competition_id'], 'Details').'</td>',
              '</tr>';
        }
        echo '</tbody></table>';

        // search for team mates
        $teammates = array();

        foreach ($scores as $score) {
            for ($wk = 1; $wk < 8; $wk++) {
                if (array_key_exists('person_'.$wk, $score) && $score['person_'.$wk] != null && $score['person_'.$wk] != $id) {
                    if (!array_key_exists($score['person_'.$wk], $teammates)) $teammates[$score['person_'.$wk]] = array();
                    $teammates[$score['person_'.$wk]][] = $score['competition_id'];
                }
            }
        }

        if (count($teammates) > 0) {

            asort($teammates);

            echo '<h3 style="clear:both">'.$name.' - Mannschaftsmitglieder</h3>';
            echo '<table class="datatable teammates"><thead><tr>',
                '<th style="width:15%">Person</th>',
                '<th style="width:7%">Läufe</th>',
                '<th style="width:77%">Wettkämpfe</th>',
              '</tr></thead><tbody>';
            foreach ($teammates as $tmId => $tmCompetitions) {
                $tmCs = array_unique($tmCompetitions);

                $comps = array();
                foreach ($tmCs as $c) {
                    $co = FSS::competition($c);
                    $comps[] = Link::competition($c,
                        $co['place'].'`'.date('y', strtotime($co['date'])),
                        $co['event'].' - '.gDate($co['date'])
                    );
                }

                echo
                '<tr>',
                  '<td>'.Link::person($tmId, 'full').'</td>',
                  '<td>'.count($tmCompetitions).'</td>',
                  '<td>'.implode(', ', $comps).'</td>',
                '</tr>';
            }
            echo '</tbody></table>';
        }

        if (in_array($key, array('la'))) {
            echo '<h3 style="clear:both">'.$name.' - Gelaufene Positionen</h3>';
            echo Chart::img('position_'.$key, array($id));
        }


    }
    echo '</div>';
}
}
