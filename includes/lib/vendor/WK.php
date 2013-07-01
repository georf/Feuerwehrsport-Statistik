<?php

class WK
{

    public static function type($wk, $sex, $key) {
        if ($key === 'la') {
            return self::la($wk);
        } elseif ($key === 'gs') {
            return self::gs($wk);
        } elseif ($key === 'fs') {
            return self::fs($wk, $sex);
        }
        return '';
    }

    public static function la($wk) {
        return self::get($wk, array(
            'Maschinist',
            'A-Länge',
            'Saugkorb',
            'B-Schlauch',
            'Strahlrohr links',
            'Verteiler',
            'Strahlrohr rechts'
        ));
    }

    public static function gs($wk) {
        return self::get($wk, array(
            'B-Schlauch',
            'Verteiler',
            'C-Schlauch',
            'Knoten',
            'D-Schlauch',
            'Läufer'
        ));
    }

    public static function fs($wk, $sex) {
        if ($sex === 'female') {
            return self::get($wk, array(
                'Leiterwand',
                'Hürde',
                'Balken',
                'Feuer'
            ));
        } else {
            return self::get($wk, array(
                'Haus',
                'Wand',
                'Balken',
                'Feuer'
            ));
        }
    }

    private static function get($wk, $wks) {
        if (isset($wks[$wk-1])) {
            return $wks[$wk-1];
        } else {
            return '';
        }
    }
}
