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
        $structuredLog = [];
        
        $currentDate = clone $clientDate;
        $structuredLog[] = [
            'date' => $currentDate->format('Y-m-d'),
            'description' => 'Data da Publicação/Intimação',
            'status' => 'info',
            'count' => null
        ];
        
        $termStart = clone $currentDate;
        $termStart->modify('+1 day');
        
        while (!Holidays::isBusinessDay($termStart->format('Y-m-d'), true, $state, $city)) {

            $reason = '';
            $dStr = $termStart->format('Y-m-d');
            $hol = Holidays::isHoliday($dStr, $state, $city);
            
            if (Holidays::isWeekend($dStr)) $reason = '(Final de Semana)';
            elseif ($hol) $reason = is_string($hol) ? "($hol)" : '(Feriado)';
            elseif (Holidays::isForensicRecess($dStr)) $reason = '(Recesso Forense)';
            
            $structuredLog[] = [
                'date' => $dStr,
                'description' => $reason,
                'status' => 'skipped',
                'count' => 'X'
            ];
            
            $termStart->modify('+1 day');
        }
        
        $currentDate = clone $termStart; 
        $daysCounted = 0;
        
        while ($daysCounted < $days) {
            $dStr = $currentDate->format('Y-m-d');
            $isBusiness = Holidays::isBusinessDay($dStr, $matter !== 'CRIMINAL', $state, $city);
            
            $reason = '';
            $hol = Holidays::isHoliday($dStr, $state, $city);
            if (Holidays::isWeekend($dStr)) $reason = '(Final de Semana)';
            elseif ($hol) $reason = is_string($hol) ? "($hol)" : '(Feriado)';
            elseif (Holidays::isForensicRecess($dStr)) $reason = '(Recesso Forense)';
            
            $increment = false;
            
            if ($type === 'working') {
                if ($isBusiness) {
                    $daysCounted++;
                    $increment = true;
                }
            } else {
                 if ($matter === 'CRIMINAL' || !Holidays::isForensicRecess($dStr)) {
                     $daysCounted++;
                     $increment = true;
                 } else {
                     $reason = '(Suspenso/Recesso)';
                 }
            }
            
            $structuredLog[] = [
                'date' => $dStr,
                'description' => $reason ? $reason : '-',
                'status' => $increment ? 'counted' : 'skipped',
                'count' => $increment ? $daysCounted : 'X'
            ];
            
            if ($daysCounted < $days) {
                $currentDate->modify('+1 day');
            }
        }
        
        
        $finalDate = clone $currentDate;
        
        while (!Holidays::isBusinessDay($finalDate->format('Y-m-d'), $matter !== 'CRIMINAL', $state, $city)) {
            $finalDate->modify('+1 day');
             $dStr = $finalDate->format('Y-m-d');
             
            $reason = '';
            $hol = Holidays::isHoliday($dStr, $state, $city);
            if (Holidays::isWeekend($dStr)) $reason = '(Final de Semana)';
            elseif ($hol) $reason = is_string($hol) ? "($hol)" : '(Feriado)';
            elseif (Holidays::isForensicRecess($dStr)) $reason = '(Recesso Forense)';
            
             $structuredLog[] = [
                'date' => $dStr,
                'description' => "Prorrogação $reason",
                'status' => 'extended',
                'count' => 'Próx. Dia Útil'
            ];
        }

        return [
            'start_date' => $startDate,
            'term_start' => $termStart->format('Y-m-d'),
            'end_date' => $finalDate->format('Y-m-d'),
            'days' => $days,
            'type' => $type,
            'matter' => $matter,
            'description' => "Vence " . self::formatDatePt($finalDate),
            'log' => $structuredLog, // New structured log
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
