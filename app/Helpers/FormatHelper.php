<?php

namespace App\Helpers;

class FormatHelper
{
    //Format the size

    public static function formatSize($bytes)
    {
        if ($bytes < 0) {
            return 'Invalid size';
        }
    
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB']; // Added 'PB' for Petabytes
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
    
        // Ensure that we don't exceed the available units
        $power = min($power, count($units) - 1);
    
        return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }

    //Format the time
    public static function formatTime($seconds)
    {
        // Constants for time calculations
        $minutesInHour = 60;
        $secondsInMinute = 60;
        $hoursInDay = 24;
        $daysInWeek = 7;
        $daysInMonth = 30;

        // Calculate the total minutes, hours, days, weeks, and months
        $minutes = floor($seconds / $secondsInMinute);
        $hours = floor($minutes / $minutesInHour);
        $days = floor($hours / $hoursInDay);
        $weeks = floor($days / $daysInWeek);
        $months = floor($days / $daysInMonth);

        // If more than 30 days, display in months, weeks, and days
        if ($days > 30) {
            $remainingDays = $days % $daysInMonth;
            $remainingWeeks = floor($remainingDays / $daysInWeek);
            $remainingDays = $remainingDays % $daysInWeek;
            return sprintf('%d months, %d weeks, %d days', $months, $remainingWeeks, $remainingDays);
        }
        // If more than 7 days, display in weeks, days, and hours
        elseif ($days >= 7) {
            $remainingDays = $days % $daysInWeek;
            $remainingHours = $hours % $hoursInDay;
            return sprintf('%d weeks, %d days, %d hours', $weeks, $remainingDays, $remainingHours);
        }
        // Otherwise, display in days, hours, and minutes
        else {
            $remainingHours = $hours % $hoursInDay;
            $remainingMinutes = $minutes % $minutesInHour;
            return sprintf('%d days, %d hours, %d minutes', $days, $remainingHours, $remainingMinutes);
        }
    }
}
