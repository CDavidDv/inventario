<?php

namespace App\Http\Controllers;


class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     *
     * @return \Inertia\Response
     */
    public function show() {
        $user = request()->user();
        
        // Obtener información del agente de usuario
        $userAgent = request()->userAgent() ?? 'Unknown';
        $isMobile = request()->header('User-Agent') ? 
            (preg_match('/Mobile|Android|iPhone|iPad/', request()->header('User-Agent')) ? true : false) : false;
        
        // Detectar navegador básico
        $browser = 'Unknown';
        if (str_contains($userAgent, 'Chrome')) $browser = 'Chrome';
        elseif (str_contains($userAgent, 'Firefox')) $browser = 'Firefox';
        elseif (str_contains($userAgent, 'Safari')) $browser = 'Safari';
        elseif (str_contains($userAgent, 'Edge')) $browser = 'Edge';
        
        // Detectar sistema operativo
        $platform = 'Unknown';
        if (str_contains($userAgent, 'Windows')) $platform = 'Windows';
        elseif (str_contains($userAgent, 'Macintosh')) $platform = 'macOS';
        elseif (str_contains($userAgent, 'Linux')) $platform = 'Linux';
        elseif (str_contains($userAgent, 'Android')) $platform = 'Android';
        elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) $platform = 'iOS';
        
        // Obtener información de geolocalización básica por IP (puedes usar un servicio como ipapi.co)
        $ipInfo = @file_get_contents('http://ip-api.com/json/' . request()->ip() . '?fields=country,regionName,city,timezone');
        $locationData = $ipInfo ? json_decode($ipInfo, true) : null;
        
        $sessions = collect([
            [
                'id' => session()->getId(),
                'agent' => [
                    'is_desktop' => !$isMobile,
                    'platform' => $platform,
                    'browser' => $browser,
                    'version' => $browser, // Puedes extraer la versión del user agent si lo necesitas
                ],
                'ip_address' => request()->ip(),
                'location' => $locationData ? [
                    'country' => $locationData['country'] ?? 'Unknown',
                    'region' => $locationData['regionName'] ?? 'Unknown', 
                    'city' => $locationData['city'] ?? 'Unknown',
                    'timezone' => $locationData['timezone'] ?? 'Unknown',
                ] : [
                    'country' => 'Unknown',
                    'region' => 'Unknown',
                    'city' => 'Unknown', 
                    'timezone' => 'Unknown',
                ],
                'is_current_device' => true,
                'last_active' => now(),
                'login_at' => $user->created_at ?? now(), // Fecha de registro como aproximación
                'user_agent' => $userAgent,
            ]
        ]);
        
        return inertia('Profile/Show', [
            'confirmsTwoFactorAuthentication' => $user ? $user->hasEnabledTwoFactorAuthentication() : false,
            'sessions' => $sessions->all(),
        ]);
        
    }
    
}