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
        // Ensure $seconds is an integer
        $seconds = (int) $seconds;
    
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

    //Short relative time, e.g. "just now", "5m ago", "2h ago", "3d ago", "2w ago"
    public static function shortRelativeTime($date)
    {
        $now = \Illuminate\Support\Carbon::now();
        $date = \Illuminate\Support\Carbon::parse($date);
        $seconds = $date->diffInSeconds($now);

        if ($seconds < 30) {
            return 'just now';
        }
        if ($seconds < 60) {
            return floor($seconds) . 's ago';
        }
        $minutes = floor($seconds / 60);
        if ($minutes < 60) {
            return $minutes . 'm ago';
        }
        $hours = floor($minutes / 60);
        if ($hours < 24) {
            return $hours . 'h ago';
        }
        $days = floor($hours / 24);
        if ($days < 7) {
            return $days . 'd ago';
        }
        $weeks = floor($days / 7);
        if ($weeks < 5) {
            return $weeks . 'w ago';
        }
        $months = floor($days / 30);
        if ($months < 12) {
            return $months . 'mo ago';
        }
        return floor($days / 365) . 'y ago';
    }
    
}
