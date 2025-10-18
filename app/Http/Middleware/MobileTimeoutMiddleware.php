<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MobileTimeoutMiddleware
{
    /**
     * Mobile device user agent patterns
     */
    private $mobileUserAgents = [
        'Mobile', 'Android', 'iPhone', 'iPad', 'iPod', 'BlackBerry', 
        'Windows Phone', 'Opera Mini', 'IEMobile', 'Mobile Safari'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $isMobile = $this->isMobileDevice($request);
        $userAgent = $request->userAgent();
        
        // Log mobile device detection for debugging
        if ($isMobile) {
            Log::info('Mobile device detected', [
                'user_agent' => $userAgent,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'method' => $request->method()
            ]);
            
            // Apply mobile-specific timeout settings
            $this->applyMobileTimeouts();
            
            // Add mobile device indicator to request
            $request->attributes->set('is_mobile', true);
            $request->attributes->set('mobile_type', $this->getMobileType($userAgent));
        }
        
        $response = $next($request);
        
        if ($isMobile) {
            // Add mobile-specific headers to response
            $response = $this->addMobileHeaders($response, $request);
        }
        
        return $response;
    }
    
    /**
     * Check if the request is from a mobile device
     */
    private function isMobileDevice(Request $request): bool
    {
        $userAgent = $request->userAgent();
        
        if (empty($userAgent)) {
            return false;
        }
        
        foreach ($this->mobileUserAgents as $mobile) {
            if (stripos($userAgent, $mobile) !== false) {
                return true;
            }
        }
        
        // Additional mobile detection patterns
        if (preg_match('/(up\.browser|up\.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $userAgent)) {
            return true;
        }
        
        // Check for tablet devices
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobile))/i', $userAgent)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get mobile device type
     */
    private function getMobileType(string $userAgent): string
    {
        if (stripos($userAgent, 'iPhone') !== false) {
            return 'iPhone';
        } elseif (stripos($userAgent, 'iPad') !== false) {
            return 'iPad';
        } elseif (stripos($userAgent, 'Android') !== false && stripos($userAgent, 'Mobile') !== false) {
            return 'Android Phone';
        } elseif (stripos($userAgent, 'Android') !== false) {
            return 'Android Tablet';
        } elseif (stripos($userAgent, 'BlackBerry') !== false) {
            return 'BlackBerry';
        } elseif (stripos($userAgent, 'Windows Phone') !== false) {
            return 'Windows Phone';
        } elseif (stripos($userAgent, 'Opera Mini') !== false) {
            return 'Opera Mini';
        }
        
        return 'Mobile';
    }
    
    /**
     * Apply mobile-specific timeout settings
     */
    private function applyMobileTimeouts(): void
    {
        // Increase execution time for mobile requests
        $mobileTimeout = config('app.mobile_timeout_multiplier', 1.5);
        $baseTimeout = ini_get('max_execution_time') ?: 30;
        $newTimeout = (int) ($baseTimeout * $mobileTimeout);
        
        ini_set('max_execution_time', $newTimeout);
        ini_set('default_socket_timeout', min($newTimeout, 180));
        ini_set('max_input_time', min($newTimeout, 120));
        
        // Set memory limit for mobile processing
        $currentMemory = ini_get('memory_limit');
        if ($currentMemory && intval($currentMemory) < 512) {
            ini_set('memory_limit', '512M');
        }
        
        Log::debug('Mobile timeout settings applied', [
            'max_execution_time' => ini_get('max_execution_time'),
            'default_socket_timeout' => ini_get('default_socket_timeout'),
            'memory_limit' => ini_get('memory_limit')
        ]);
    }
    
    /**
     * Add mobile-specific headers to response
     */
    private function addMobileHeaders($response, Request $request)
    {
        $mobileType = $request->attributes->get('mobile_type', 'Mobile');
        
        $response->headers->set('X-Mobile-Detected', 'true');
        $response->headers->set('X-Mobile-Type', $mobileType);
        $response->headers->set('X-Mobile-Optimized', 'true');
        
        // Connection optimization headers
        $response->headers->set('Connection', 'keep-alive');
        
        // Cache control for mobile
        if (!$response->headers->has('Cache-Control')) {
            $response->headers->set('Cache-Control', 'no-cache, must-revalidate, max-age=300');
        }
        
        // Add retry information for failed requests
        if ($response->getStatusCode() >= 500) {
            $response->headers->set('Retry-After', '30');
            $response->headers->set('X-Mobile-Retry-Recommended', 'true');
        }
        
        return $response;
    }
    
    /**
     * Check if request should have extended timeout
     */
    private function requiresExtendedTimeout(Request $request): bool
    {
        $extendedPaths = [
            '/api/live-statistics',
            '/admin/dashboard',
            '/user/dashboard',
            '/payment/process',
            '/share/purchase'
        ];
        
        $currentPath = $request->path();
        
        foreach ($extendedPaths as $path) {
            if (stripos($currentPath, trim($path, '/')) !== false) {
                return true;
            }
        }
        
        return false;
    }
}