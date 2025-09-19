<?php

if (!function_exists('safe_username')) {
    /**
     * Safely display username with fallback
     *
     * @param mixed $user User object or null
     * @param string $fallback Fallback text if username is not available
     * @return string
     */
    function safe_username($user, $fallback = 'N/A')
    {
        if (!$user) {
            return $fallback;
        }
        
        if (is_object($user) && property_exists($user, 'username')) {
            return $user->username ?? $fallback;
        }
        
        if (is_array($user) && isset($user['username'])) {
            return $user['username'] ?? $fallback;
        }
        
        return $fallback;
    }
}

if (!function_exists('safe_auth_username')) {
    /**
     * Safely get authenticated user's username
     *
     * @param string $fallback Fallback text if user is not authenticated or username is missing
     * @return string
     */
    function safe_auth_username($fallback = 'Guest')
    {
        if (!Auth::check()) {
            return $fallback;
        }
        
        return Auth::user()->username ?? $fallback;
    }
}

if (!function_exists('safe_user_display')) {
    /**
     * Safely display user information with username and name
     *
     * @param mixed $user User object or null
     * @param bool $includeAt Whether to include @ symbol before username
     * @return string
     */
    function safe_user_display($user, $includeAt = true)
    {
        if (!$user) {
            return 'Guest User';
        }
        
        $name = $user->name ?? 'Unknown';
        $username = safe_username($user, 'unknown');
        $at = $includeAt ? '@' : '';
        
        return "{$name} ({$at}{$username})";
    }
}
