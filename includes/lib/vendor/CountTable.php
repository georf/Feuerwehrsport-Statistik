<?php

class CountTable {
    public static function get($type, $entries) {
        $c = '<table class="datatable">';
        $c .= '<thead><tr>';
        $c .= '<th style="width:80%">';
        $c .= ($type == 'place')?'Ort':'Typ';
        $c .= '</th>';
        $c .= '<th style="width:20%">Wettkämpfe</th>';
        $c .= '</tr></thead>';
        $c .= '<tbody>';

        foreach ($entries as $entry) {
            $c .= '<tr><td>';
            $c .= call_user_func('Link::'.$type, $entry['id'], $entry['name']);
            $c .= '</td><td>'.$entry['count'].'</td></tr>';
        }

        $c .= '</tbody></table>';

        return $c;
    }
}
