<?php
// src/DeadlineCalculator.php

require_once __DIR__ . '/Holidays.php';

class DeadlineCalculator {

    /**
     * Calculate legal deadline
     * @param string $startDate Y-m-d (Data da Publicação/Intimação)
     * @param int $days Number of days
     * @param string $type 'working' or 'calendar'
     * @param string|null $state UF (e.g., 'SP')
     * @param string|null $city City ID (e.g., 'SAO_PAULO')
     * @param string|null $matter Legal area (e.g., 'CRIMINAL', 'CIVIL', 'LABOR')
     * @return array Result details
     */
    public static function calculate($startDate, $days, $type = 'working', $state = null, $city = null, $matter = null, $cityName = null) {
        $clientDate = new DateTime($startDate);
        $log = [];
        
        $currentDate = clone $clientDate;
        $log[] = "Publicação: " . $currentDate->format('d/m/Y');

        $currentDate->modify('+1 day');
        
        $considerRecess = ($matter !== 'CRIMINAL');
        while (!Holidays::isBusinessDay($currentDate->format('Y-m-d'), $considerRecess, $state, $city)) {
             $reason = '';
             $hol = Holidays::isHoliday($currentDate->format('Y-m-d'), $state, $city);
             
             if (Holidays::isWeekend($currentDate->format('Y-m-d'))) $reason = 'Fim de semana';
             elseif ($hol) $reason = $hol === true ? 'Feriado' : $hol;
             elseif (Holidays::isForensicRecess($currentDate->format('Y-m-d'))) $reason = 'Recesso';
             
             $log[] = "Pula " . $currentDate->format('d/m/Y') . " ($reason)";
             $currentDate->modify('+1 day');
        }

        $termStart = clone $currentDate;
        $log[] = "Início da contagem: " . $termStart->format('d/m/Y');

        $daysCounted = 0;
        
        if ($days > 0) {
            $daysCounted = 1; 
        }

        while ($daysCounted < $days) {
            $currentDate->modify('+1 day');
            $dateStr = $currentDate->format('Y-m-d');
            
            if ($type === 'working') {
                if (Holidays::isBusinessDay($dateStr, true, $state, $city)) {
                    $daysCounted++;
                } else {
                     $reason = '';
                     $hol = Holidays::isHoliday($dateStr, $state, $city);
                     if (Holidays::isWeekend($dateStr)) $reason = 'Fim de semana';
                     elseif ($hol) $reason = $hol === true ? 'Feriado' : $hol;
                     elseif (Holidays::isForensicRecess($dateStr)) $reason = 'Recesso';
                }
            } else {
                if ($matter === 'CRIMINAL') {
                    $daysCounted++;
                } else {
                    if (Holidays::isForensicRecess($dateStr)) {
                    } else {
                        $daysCounted++;
                    }
                }
            }
        }

        $finalPush = false;
        while (!Holidays::isBusinessDay($currentDate->format('Y-m-d'), $considerRecess, $state, $city)) {
            $reason = '';
             $hol = Holidays::isHoliday($currentDate->format('Y-m-d'), $state, $city);
             
             if (Holidays::isWeekend($currentDate->format('Y-m-d'))) $reason = 'Fim de semana';
             elseif ($hol) $reason = $hol === true ? 'Feriado' : $hol;
             elseif (Holidays::isForensicRecess($currentDate->format('Y-m-d'))) $reason = 'Recesso';
             
             $log[] = "Vencimento em " . $currentDate->format('d/m/Y') . " prorrogado ($reason)";
             $currentDate->modify('+1 day');
             $finalPush = true;
        }

        $formattedEnd = $currentDate->format('d/m/Y');
        $log[] = "Prazo Final: " . $formattedEnd;

        return [
            'start_date' => $startDate,
            'term_start' => $termStart->format('Y-m-d'),
            'end_date' => $currentDate->format('Y-m-d'),
            'days' => $days,
            'type' => $type,
            'matter' => $matter,
            'description' => "Vence " . self::formatDatePt($currentDate),
            'log' => $log,
            'location' => $cityName ? "$cityName - $state" : ($state ? "Estado de $state" : 'Nacional')
        ];
    }

    private static function formatDatePt($date) {
        $days = [
            'Sunday' => 'Domingo',
            'Monday' => 'Segunda-feira',
            'Tuesday' => 'Terça-feira',
            'Wednesday' => 'Quarta-feira',
            'Thursday' => 'Quinta-feira',
            'Friday' => 'Sexta-feira',
            'Saturday' => 'Sábado'
        ];
        $dayName = $days[$date->format('l')];
        return $dayName . ', ' . $date->format('d/m/Y');
    }
}
