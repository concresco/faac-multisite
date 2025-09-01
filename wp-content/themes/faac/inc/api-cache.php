<?php
/**
 * API Cache Helper
 * Caches API responses to avoid 429 Too Many Requests errors
 */

function get_cached_api_response($url, $cache_time = 3600) {
    // Create cache directory if it doesn't exist
    $cache_dir = WP_CONTENT_DIR . '/cache/api/';
    if (!file_exists($cache_dir)) {
        wp_mkdir_p($cache_dir);
    }
    
    // Generate cache key from URL
    $cache_key = md5($url);
    $cache_file = $cache_dir . $cache_key . '.json';
    
    // ALWAYS use cache if it exists (ignore expiration time due to rate limiting)
    if (file_exists($cache_file)) {
        $cached_data = file_get_contents($cache_file);
        $data = json_decode($cached_data, true);
        
        // Only try to refresh if cache is older than cache_time
        if (time() - filemtime($cache_file) < $cache_time) {
            return $data;
        }
        
        // Try to refresh but return cached data if fails
        $new_data = fetch_from_pim($url, $cache_file);
        return $new_data !== false ? $new_data : $data;
    }
    
    // If no cache, try to fetch from PIM
    $data = fetch_from_pim($url, $cache_file);
    
    // Return data if successful, otherwise empty array
    return $data !== false ? $data : array();
}

// Helper function to fetch from PIM with better error handling
function fetch_from_pim($url, $cache_file = null) {
    // Add headers to avoid rate limiting
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'ignore_errors' => true,
            'header' => [
                'Accept: application/json',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Cache-Control: no-cache',
                'X-Forwarded-For: ' . $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'X-Real-IP: ' . $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    // Check response headers for rate limiting
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (strpos($header, '429') !== false || strpos($header, 'x-ratelimit-remaining: 0') !== false) {
                error_log("PIM API Rate Limited: " . $url);
                return false;
            }
        }
    }
    
    if ($response !== false && $cache_file) {
        file_put_contents($cache_file, $response);
    }
    
    return $response ? json_decode($response, true) : false;
} 