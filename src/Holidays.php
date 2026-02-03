<?php
// src/Holidays.php

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

        // Mobile holidays (Pascoa based)
        $easterDate = easter_date($year);
        $easterDay = date('j', $easterDate);
        $easterMonth = date('n', $easterDate);
        $easterYear = date('Y', $easterDate);

        // Carnaval (47 days before Easter) - Facuultative point usually, but often courts close
        // Lets treat Carnaval Mon/Tue as non-business for safety or keep it strict?
        // Standard national holidays usually count Carnaval as Facultative.
        // Courts usually close Monday and Tuesday. Adding them for safety.
        $carnaval = date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay - 47, $easterYear));
        $carnaval2 = date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay - 46, $easterYear));
        
        // Sexta-feira Santa (2 days before Easter)
        $goodFriday = date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay - 2, $easterYear));
        
        // Corpus Christi (60 days after Easter)
        $corpusChristi = date('Y-m-d', mktime(0, 0, 0, $easterMonth, $easterDay + 60, $easterYear));

        $holidays[$carnaval] = 'Carnaval';
        $holidays[$carnaval2] = 'Carnaval';
        $holidays[$goodFriday] = 'Paixão de Cristo';
        $holidays[$corpusChristi] = 'Corpus Christi';

        return $holidays;
    }

    public static function isHoliday($date) {
        $year = date('Y', strtotime($date));
        $holidays = self::getHolidays($year);
        return array_key_exists($date, $holidays);
    }

    /**
     * Checks for "Recesso Forense" (Dec 20 to Jan 20)
     * Note: This suspends DEADLINES (Prazos).
     * However, courts are physically closed usually Dec 20 to Jan 6.
     * New CPC says deadlines suspend Dec 20 - Jan 20.
     */
    public static function isForensicRecess($date) {
        $md = date('m-d', strtotime($date));
        // Dec 20 to Dec 31
        if ($md >= '12-20') return true;
        // Jan 01 to Jan 20
        if ($md <= '01-20') return true;
        
        return false;
    }

    public static function isWeekend($date) {
        $w = date('w', strtotime($date));
        return ($w == 0 || $w == 6); // 0 = Sunday, 6 = Saturday
    }

    public static function isBusinessDay($date, $considerRecess = true) {
        if (self::isWeekend($date)) return false;
        if (self::isHoliday($date)) return false;
        if ($considerRecess && self::isForensicRecess($date)) return false;
        return true;
    }
}
