<?php
/**
 * PHP 8 Compatibility Functions
 * Provides PHP 8 functions for older PHP versions
 */

if (!function_exists('str_starts_with')) {
    /**
     * Checks if a string starts with a given substring
     * 
     * @param string $haystack The string to search in
     * @param string $needle The substring to search for
     * @return bool Returns true if haystack starts with needle, false otherwise
     */
    function str_starts_with(string $haystack, string $needle): bool {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }
}

if (!function_exists('str_ends_with')) {
    /**
     * Checks if a string ends with a given substring
     * 
     * @param string $haystack The string to search in
     * @param string $needle The substring to search for
     * @return bool Returns true if haystack ends with needle, false otherwise
     */
    function str_ends_with(string $haystack, string $needle): bool {
        return substr($haystack, -strlen($needle)) === $needle;
    }
}

if (!function_exists('str_contains')) {
    /**
     * Checks if a string contains a given substring
     * 
     * @param string $haystack The string to search in
     * @param string $needle The substring to search for
     * @return bool Returns true if haystack contains needle, false otherwise
     */
    function str_contains(string $haystack, string $needle): bool {
        return strpos($haystack, $needle) !== false;
    }
}
