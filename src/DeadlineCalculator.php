<?php
// src/DeadlineCalculator.php

require_once __DIR__ . '/Holidays.php';

class DeadlineCalculator {

    /**
     * Calculate legal deadline
     * @param string $startDate Y-m-d (Data da Publicação/Intimação)
     * @param int $days Number of days
     * @param string $type 'working' or 'calendar'
     * @return array Result details
     */
    public static function calculate($startDate, $days, $type = 'working') {
        $clientDate = new DateTime($startDate);
        $log = [];
        
        // 1. Determine Start of Count (Termo Inicial)
        // Rule: Exclude the start day, start counting next business day.
        
        $currentDate = clone $clientDate;
        $log[] = "Publicação: " . $currentDate->format('d/m/Y');

        // Step 1: Advance one day (Day 0 + 1)
        $currentDate->modify('+1 day');
        
        // Rule: If the resulting day is not a business day, keep advancing until it is.
        // Note: For "Calendar Days" (Prazos Materiais/Corridos), usually it DOES start on next day even if weekend?
        // Actually, typically in Law:
        // "O prazo começa a correr a partir do primeiro dia útil após a publicação."
        // This applies to both types usually implies the 'Termo Inicial' must be a handy day for the lawyer to see it.
        // Let's assume standard procedural rule for start date for both.
        
        while (!Holidays::isBusinessDay($currentDate->format('Y-m-d'), true)) {
             $reason = '';
             if (Holidays::isWeekend($currentDate->format('Y-m-d'))) $reason = 'Fim de semana';
             elseif (Holidays::isHoliday($currentDate->format('Y-m-d'))) $reason = 'Feriado';
             elseif (Holidays::isForensicRecess($currentDate->format('Y-m-d'))) $reason = 'Recesso';
             
             $log[] = "Pula " . $currentDate->format('d/m/Y') . " ($reason)";
             $currentDate->modify('+1 day');
        }

        $termStart = clone $currentDate;
        $log[] = "Início da contagem: " . $termStart->format('d/m/Y');

        // 2. Count the days
        // We already positioned $currentDate at the first valid 'Termo Inicial' (Start Day).
        // So this day MUST count as Day 1 (if working days logic applies).
        
        $daysCounted = 0;
        
        // Check Day 1 (TermStart)
        if ($type === 'working') {
             // We know TermStart is a business day because of the loop above? 
             // Yes, unless days=0 (edge case).
             if ($days > 0) {
                 $daysCounted = 1; 
             }
        } else {
             if ($days > 0) {
                 $daysCounted = 1;
             }
        }

        // Loop for remaining days
        while ($daysCounted < $days) {
            $currentDate->modify('+1 day');
            $dateStr = $currentDate->format('Y-m-d');
            
            if ($type === 'working') {
                if (Holidays::isBusinessDay($dateStr, true)) {
                    $daysCounted++;
                }
            } else {
                $daysCounted++;
            }
        }

        // 3. Check End Date (Termo Final)
        // If it lands on a non-business day (always applies), push to next business day.
        $finalPush = false;
        while (!Holidays::isBusinessDay($currentDate->format('Y-m-d'), true)) {
            $reason = '';
             if (Holidays::isWeekend($currentDate->format('Y-m-d'))) $reason = 'Fim de semana';
             elseif (Holidays::isHoliday($currentDate->format('Y-m-d'))) $reason = 'Feriado';
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
            'description' => "Vence " . $currentDate->format('l, d/m/Y'),
            'log' => $log
        ];
    }
}
