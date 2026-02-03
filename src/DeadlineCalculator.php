<?php
// src/DeadlineCalculator.php

require_once __DIR__ . '/Holidays.php';

class DeadlineCalculator {

    /**
     * Calculate legal deadline
     * @param string $startDate Y-m-d (Data da Publicação/Intimação)
     * @param int $days Number of days
     * @param string|null $state UF (e.g., 'SP')
     * @param string|null $city City ID (e.g., 'SAO_PAULO')
     * @return array Result details
     */
    public static function calculate($startDate, $days, $type = 'working', $state = null, $city = null) {
        $clientDate = new DateTime($startDate);
        $log = [];
        
        // 1. Determine Start of Count (Termo Inicial)
        $currentDate = clone $clientDate;
        $log[] = "Publicação: " . $currentDate->format('d/m/Y');

        // Step 1: Advance one day (Day 0 + 1)
        $currentDate->modify('+1 day');
        
        // Wait for business day to start counting
        while (!Holidays::isBusinessDay($currentDate->format('Y-m-d'), true, $state, $city)) {
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

        // 2. Count the days
        $daysCounted = 0;
        
        // Check Day 1 (TermStart)
        if ($days > 0) {
            $daysCounted = 1; 
        }

        // Loop for remaining days
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
                     
                     // Optional: Add to log for clarity on skipped days (only if verbose)
                     // $log[] = "Dia " . $currentDate->format('d/m/Y') . " não contado ($reason)";
                }
            } else {
                // Calendar days: check for recess if needed?
                // Usually Calendar days running during recess? 
                // Novo CPC Art 220: "Suspende-se o curso do prazo processual nos dias compreendidos entre 20 de dezembro e 20 de janeiro."
                // This suspension applies to ALL procedural deadlines, even calendar ones (like penal)? 
                // Controversial. But for standardized tool, usually we suspend.
                
                if (Holidays::isForensicRecess($dateStr)) {
                    // Suspended
                } else {
                    $daysCounted++;
                }
            }
        }

        // 3. Check End Date (Termo Final)
        // If it lands on a non-business day (always applies), push to next business day.
        $finalPush = false;
        while (!Holidays::isBusinessDay($currentDate->format('Y-m-d'), true, $state, $city)) {
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
            'description' => "Vence " . self::formatDatePt($currentDate),
            'log' => $log,
            'location' => $state ? "$city, $state" : 'Nacional'
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
