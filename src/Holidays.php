<?php

class Holidays {

    public static function getHolidays($year) {
        $holidays = [
            $year . '-01-01' => 'Confraternização Universal',
            $year . '-04-21' => 'Tiradentes',
            $year . '-05-01' => 'Dia do Trabalho',
            $year . '-09-07' => 'Independência do Brasil',
            $year . '-10-12' => 'Nossa Senhora Aparecida',
            $year . '-11-02' => 'Finados',
            $year . '-11-15' => 'Proclamação da República',
            $year . '-12-25' => 'Natal',
        ];

        $easterDate = easter_date($year);
        $easterDay = date('j', $easterDate);
        $easterMonth = date('n', $easterDate);
        $easterYear = date('Y', $easterDate);

        $carnaval = date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay - 47, $easterYear));
        $carnaval2 = date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay - 46, $easterYear));
        
        $goodFriday = date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay - 2, $easterYear));
        
        $corpusChristi = date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay + 60, $easterYear));

        $holidays[$carnaval] = 'Carnaval';
        $holidays[$carnaval2] = 'Carnaval';
        $holidays[$goodFriday] = 'Paixão de Cristo';
        $holidays[$corpusChristi] = 'Corpus Christi';

        return $holidays;
    }

    private static $localHolidays = null;

    private static function loadRules() {
        if (self::$localHolidays === null) {
            $file = __DIR__ . '/../data/jurisdictions.json';
            if (file_exists($file)) {
                $data = json_decode(file_get_contents($file), true);
                self::$localHolidays = $data['holidays'] ?? [];
            } else {
                self::$localHolidays = [];
            }
        }
    }

    public static function isHoliday($date, $state = null, $city = null) {
        $year = date('Y', strtotime($date));
        
        // 1. National
        $holidays = self::getHolidays($year);
        if (array_key_exists($date, $holidays)) return $holidays[$date];

        // 2. Local
        self::loadRules();
        
        // Example check: AC (State)
        if ($state && isset(self::$localHolidays[$state])) {
            if (in_array($date, self::$localHolidays[$state])) return "Feriado Estadual ($state)";
        }

        // Example check: BRASILEIA (City)
        if ($city && isset(self::$localHolidays[$city])) {
            if (in_array($date, self::$localHolidays[$city])) return "Feriado Municipal ($city)";
        }

        return false;
    }

    public static function isForensicRecess($date) {
        $m = (int)date('m', strtotime($date));
        $d = (int)date('d', strtotime($date));
        
        if ($m == 12 && $d >= 20) return true;
        if ($m == 1 && $d <= 20) return true;
        
        return false;
    }

    public static function isWeekend($date) {
        $w = date('w', strtotime($date));
        return ($w == 0 || $w == 6);
    }

    public static function isBusinessDay($date, $considerRecess = true, $state = null, $city = null) {
        if (self::isWeekend($date)) return false;
        if (self::isHoliday($date, $state, $city)) return false;
        if ($considerRecess && self::isForensicRecess($date)) return false;
        return true;
    }
}
