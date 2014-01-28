<?php

class CountTable {
    public static function get($type, $entries, $id = 'id') {
        $c = '<table class="datatable">';
        $c .= '<thead><tr>';
        $c .= '<th style="width:80%">';
        $c .= Title::name($type);
        $c .= '</th>';
        $c .= '<th style="width:20%">Wettkämpfe</th>';
        $c .= '</tr></thead>';
        $c .= '<tbody>';

        foreach ($entries as $entry) {
            $c .= '<tr><td>';
            if (isset($entry['name'])) {
                $c .= call_user_func('Link::'.$type, $entry[$id], $entry['name']);
            } else {
                $c .= call_user_func('Link::'.$type, $entry[$id]);
            }
            $c .= '</td><td>'.$entry['count'].'</td></tr>';
        }

        $c .= '</tbody></table>';

        return $c;
    }
}
