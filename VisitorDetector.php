<?php

declare(strict_types=1);

/**
 * VisitorDetector Class - Deteksi detail pengunjung website
 * 
 * Features:
 * - Deteksi Country dengan Cloudflare & fallback API
 * - Deteksi Browser & OS
 * - Deteksi Device (Mobile/Desktop/Tablet)
 * - Deteksi Bot/Crawler
 * - IP Address tracking
 * - Timezone detection
 * - Language detection
 * 
 * @author Your Name
 * @version 1.0
 * @requires PHP 8.3+
 */
class VisitorDetector
{
    private string $userAgent;
    private string $ipAddress;
    private array $serverData;
    private array $geoCache = [];
    private array $countryNames = [];

    public function __construct(array $serverData = null)
    {
        $this->serverData = $serverData ?? $_SERVER;
        $this->userAgent = $this->serverData['HTTP_USER_AGENT'] ?? '';
        $this->ipAddress = $this->getClientIP();
        $this->initCountryNames();
    }

    /**
     * Get all visitor details in one call
     */
    public function getVisitorDetails(): array
    {
        return [
            'ip' => $this->getIP(),
            'location' => $this->getLocation(),
            'browser' => $this->getBrowser(),
            'os' => $this->getOperatingSystem(),
            'device' => $this->getDevice(),
            'language' => $this->getLanguage(),
            'timezone' => $this->getTimezone(),
            'is_bot' => $this->isBot(),
            'is_mobile' => $this->isMobile(),
            'referrer' => $this->getReferrer(),
            'timestamp' => date('Y-m-d H:i:s'),
            'user_agent' => $this->userAgent
        ];
    }

    /**
     * Get client IP address with proxy support
     */
    public function getIP(): string
    {
        return $this->ipAddress;
    }

    private function getClientIP(): string
    {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP',    // Cloudflare
            'HTTP_X_FORWARDED_FOR',     // Load balancer/proxy
            'HTTP_X_REAL_IP',           // Nginx proxy
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (!empty($this->serverData[$key])) {
                $ip = trim(explode(',', $this->serverData[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $this->serverData['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Get geographical location using Cloudflare headers with API fallback
     */
    public function getLocation(): array
    {
        // Try Cloudflare first (fastest and most reliable)
        if ($cloudflareData = $this->getCloudflareGeoData()) {
            return $cloudflareData;
        }

        // Fallback to external API
        return $this->getLocationFromAPI();
    }

    private function getCloudflareGeoData(): array|false
    {
        $country = $this->serverData['HTTP_CF_IPCOUNTRY'] ?? null;
        
        if (!$country || $country === 'XX') {
            return false;
        }

        return [
            'country_code' => $country,
            'country' => $this->getCountryName($country),
            'city' => $this->serverData['HTTP_CF_IPCITY'] ?? null,
            'region' => $this->serverData['HTTP_CF_REGION'] ?? null,
            'region_code' => $this->serverData['HTTP_CF_REGION_CODE'] ?? null,
            'postal_code' => $this->serverData['HTTP_CF_POSTAL_CODE'] ?? null,
            'timezone' => $this->serverData['HTTP_CF_TIMEZONE'] ?? null,
            'latitude' => $this->serverData['HTTP_CF_LATITUDE'] ?? null,
            'longitude' => $this->serverData['HTTP_CF_LONGITUDE'] ?? null,
            'continent' => $this->serverData['HTTP_CF_IPCONTINENT'] ?? null,
            'asn' => $this->serverData['HTTP_CF_ASN'] ?? null,
            'data_center' => $this->serverData['HTTP_CF_COLO'] ?? null,
            'source' => 'cloudflare'
        ];
    }

    private function getLocationFromAPI(): array
    {
        if (isset($this->geoCache[$this->ipAddress])) {
            return $this->geoCache[$this->ipAddress];
        }

        // Skip localhost
        if (in_array($this->ipAddress, ['127.0.0.1', '::1', 'localhost'])) {
            return [
                'country_code' => 'LOCAL',
                'country' => 'Localhost',
                'city' => 'Local',
                'source' => 'local'
            ];
        }

        // Try ip-api.com
        try {
            $url = "http://ip-api.com/json/{$this->ipAddress}?fields=status,country,countryCode,region,regionName,city,timezone,lat,lon,isp,org,as";
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'user_agent' => 'Mozilla/5.0 (compatible; VisitorDetector/1.0)'
                ]
            ]);

            $response = file_get_contents($url, false, $context);
            
            if ($response) {
                $data = json_decode($response, true);
                
                if ($data && $data['status'] === 'success') {
                    $result = [
                        'country_code' => $data['countryCode'],
                        'country' => $data['country'],
                        'city' => $data['city'],
                        'region' => $data['regionName'],
                        'timezone' => $data['timezone'] ?? null,
                        'latitude' => $data['lat'] ?? null,
                        'longitude' => $data['lon'] ?? null,
                        'isp' => $data['isp'] ?? null,
                        'organization' => $data['org'] ?? null,
                        'as' => $data['as'] ?? null,
                        'source' => 'ip-api'
                    ];
                    
                    $this->geoCache[$this->ipAddress] = $result;
                    return $result;
                }
            }
        } catch (Exception $e) {
            // Silent fail, try next method
        }

        // Fallback to ipinfo.io
        try {
            $url = "https://ipinfo.io/{$this->ipAddress}/json";
            $response = file_get_contents($url, false, stream_context_create([
                'http' => ['timeout' => 3]
            ]));
            
            if ($response) {
                $data = json_decode($response, true);
                
                if ($data && !isset($data['error'])) {
                    $result = [
                        'country_code' => $data['country'] ?? 'Unknown',
                        'country' => $this->getCountryName($data['country'] ?? ''),
                        'city' => $data['city'] ?? null,
                        'region' => $data['region'] ?? null,
                        'timezone' => $data['timezone'] ?? null,
                        'coordinates' => $data['loc'] ?? null,
                        'organization' => $data['org'] ?? null,
                        'source' => 'ipinfo'
                    ];
                    
                    $this->geoCache[$this->ipAddress] = $result;
                    return $result;
                }
            }
        } catch (Exception $e) {
            // Silent fail
        }

        return [
            'country_code' => 'Unknown',
            'country' => 'Unknown',
            'source' => 'none'
        ];
    }

    /**
     * Detect browser information
     */
    public function getBrowser(): array
    {
        $browsers = [
            'Edg' => 'Microsoft Edge',
            'Chrome' => 'Google Chrome',
            'Firefox' => 'Mozilla Firefox',
            'Safari' => 'Apple Safari',
            'Opera' => 'Opera',
            'OPR' => 'Opera',
            'Vivaldi' => 'Vivaldi',
            'Brave' => 'Brave',
            'UC Browser' => 'UC Browser',
            'Samsung' => 'Samsung Internet'
        ];

        $version = 'Unknown';
        $name = 'Unknown';

        foreach ($browsers as $key => $browser) {
            if (str_contains($this->userAgent, $key)) {
                $name = $browser;
                
                // Extract version
                if (preg_match("/{$key}\/([0-9.]+)/i", $this->userAgent, $matches)) {
                    $version = $matches[1];
                }
                break;
            }
        }

        return [
            'name' => $name,
            'version' => $version,
            'full_name' => $name !== 'Unknown' ? "{$name} {$version}" : 'Unknown'
        ];
    }

    /**
     * Detect operating system
     */
    public function getOperatingSystem(): array
    {
        $os_array = [
            'windows nt 11' => 'Windows 11',
            'windows nt 10' => 'Windows 10',
            'windows nt 6.3' => 'Windows 8.1',
            'windows nt 6.2' => 'Windows 8',
            'windows nt 6.1' => 'Windows 7',
            'windows nt 6.0' => 'Windows Vista',
            'windows nt 5.2' => 'Windows Server 2003/XP x64',
            'windows nt 5.1' => 'Windows XP',
            'windows xp' => 'Windows XP',
            'windows nt 5.0' => 'Windows 2000',
            'windows me' => 'Windows ME',
            'win98' => 'Windows 98',
            'win95' => 'Windows 95',
            'win16' => 'Windows 3.11',
            'macintosh|mac os x' => 'Mac OS X',
            'mac_powerpc' => 'Mac OS 9',
            'ubuntu' => 'Ubuntu',
            'debian' => 'Debian',
            'fedora' => 'Fedora',
            'centos' => 'CentOS',
            'red hat' => 'Red Hat',
            'linux' => 'Linux',
            'unix' => 'Unix',
            'sun os' => 'SunOS',
            'freebsd' => 'FreeBSD',
            'openbsd' => 'OpenBSD',
            'android' => 'Android',
            'iphone|ipad|ipod' => 'iOS',
            'blackberry' => 'BlackBerry',
            'webos' => 'Mobile'
        ];

        $userAgent = strtolower($this->userAgent);
        $name = 'Unknown OS';
        $version = '';

        foreach ($os_array as $regex => $os) {
            if (preg_match("/{$regex}/i", $userAgent)) {
                $name = $os;
                
                // Try to extract version
                if ($os === 'Android' && preg_match('/android ([0-9.]+)/i', $userAgent, $matches)) {
                    $version = $matches[1];
                } elseif ($os === 'iOS' && preg_match('/os ([0-9_]+)/i', $userAgent, $matches)) {
                    $version = str_replace('_', '.', $matches[1]);
                } elseif (str_contains($os, 'Windows') && preg_match('/windows nt ([0-9.]+)/i', $userAgent, $matches)) {
                    $version = $matches[1];
                }
                break;
            }
        }

        return [
            'name' => $name,
            'version' => $version,
            'full_name' => $version ? "{$name} {$version}" : $name
        ];
    }

    /**
     * Detect device type
     */
    public function getDevice(): array
    {
        $is_mobile = $this->isMobile();
        $is_tablet = $this->isTablet();
        
        if ($is_tablet) {
            $type = 'tablet';
        } elseif ($is_mobile) {
            $type = 'mobile';
        } else {
            $type = 'desktop';
        }

        return [
            'type' => $type,
            'is_mobile' => $is_mobile,
            'is_tablet' => $is_tablet,
            'is_desktop' => $type === 'desktop'
        ];
    }

    /**
     * Check if visitor is using mobile device
     */
    public function isMobile(): bool
    {
        $mobileAgents = [
            'Mobile', 'Android', 'iPhone', 'iPad', 'iPod', 'BlackBerry', 
            'webOS', 'Windows Phone', 'Windows Mobile', 'IEMobile', 
            'Opera Mini', 'Opera Mobi'
        ];

        foreach ($mobileAgents as $agent) {
            if (str_contains($this->userAgent, $agent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if visitor is using tablet
     */
    public function isTablet(): bool
    {
        $tabletAgents = ['iPad', 'Android.*Tablet', 'Tablet', 'Kindle', 'Silk', 'PlayBook'];
        
        foreach ($tabletAgents as $agent) {
            if (preg_match("/{$agent}/i", $this->userAgent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect if visitor is a bot/crawler
     */
    public function isBot(): bool
    {
        $bots = [
            'bot', 'crawler', 'spider', 'scraper', 'facebook', 'google', 
            'bing', 'yahoo', 'baidu', 'yandex', 'duckduckgo', 'archive',
            'wget', 'curl', 'python', 'java', 'go-http', 'axios'
        ];

        $userAgent = strtolower($this->userAgent);
        
        foreach ($bots as $bot) {
            if (str_contains($userAgent, $bot)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get visitor's preferred language
     */
    public function getLanguage(): array
    {
        $acceptLanguage = $this->serverData['HTTP_ACCEPT_LANGUAGE'] ?? '';
        
        if (empty($acceptLanguage)) {
            return [
                'primary' => 'en',
                'full' => 'en-US',
                'all' => ['en-US']
            ];
        }

        $languages = [];
        $primaryLang = '';
        
        // Parse Accept-Language header
        $langs = explode(',', $acceptLanguage);
        
        foreach ($langs as $lang) {
            $lang = trim($lang);
            
            // Extract language code and quality
            if (preg_match('/([a-z]{2}(?:-[A-Z]{2})?)(;q=([0-9.]+))?/i', $lang, $matches)) {
                $langCode = $matches[1];
                $quality = isset($matches[3]) ? (float)$matches[3] : 1.0;
                
                $languages[] = [
                    'code' => $langCode,
                    'quality' => $quality
                ];
                
                if (empty($primaryLang)) {
                    $primaryLang = explode('-', $langCode)[0];
                }
            }
        }

        // Sort by quality
        usort($languages, fn($a, $b) => $b['quality'] <=> $a['quality']);

        return [
            'primary' => $primaryLang ?: 'en',
            'full' => $languages[0]['code'] ?? 'en-US',
            'all' => array_column($languages, 'code')
        ];
    }

    /**
     * Get timezone information
     */
    public function getTimezone(): array
    {
        // Try Cloudflare timezone first
        $timezone = $this->serverData['HTTP_CF_TIMEZONE'] ?? null;
        
        if (!$timezone) {
            // Fallback to location-based timezone detection
            $location = $this->getLocation();
            $timezone = $location['timezone'] ?? null;
        }

        if ($timezone) {
            try {
                $tz = new DateTimeZone($timezone);
                $now = new DateTime('now', $tz);
                
                return [
                    'timezone' => $timezone,
                    'offset' => $now->format('P'),
                    'offset_seconds' => $tz->getOffset($now),
                    'current_time' => $now->format('Y-m-d H:i:s'),
                    'is_dst' => (bool)$now->format('I')
                ];
            } catch (Exception $e) {
                // Invalid timezone
            }
        }

        return [
            'timezone' => 'UTC',
            'offset' => '+00:00',
            'offset_seconds' => 0,
            'current_time' => gmdate('Y-m-d H:i:s'),
            'is_dst' => false
        ];
    }

    /**
     * Get referrer information
     */
    public function getReferrer(): array
    {
        $referrer = $this->serverData['HTTP_REFERER'] ?? '';
        
        if (empty($referrer)) {
            return [
                'url' => null,
                'domain' => null,
                'is_search_engine' => false,
                'search_engine' => null
            ];
        }

        $domain = parse_url($referrer, PHP_URL_HOST);
        
        // Detect search engines
        $searchEngines = [
            'google' => 'Google',
            'bing' => 'Bing',
            'yahoo' => 'Yahoo',
            'duckduckgo' => 'DuckDuckGo',
            'yandex' => 'Yandex',
            'baidu' => 'Baidu'
        ];

        $searchEngine = null;
        $isSearchEngine = false;

        foreach ($searchEngines as $engine => $name) {
            if ($domain && str_contains($domain, $engine)) {
                $searchEngine = $name;
                $isSearchEngine = true;
                break;
            }
        }

        return [
            'url' => $referrer,
            'domain' => $domain,
            'is_search_engine' => $isSearchEngine,
            'search_engine' => $searchEngine
        ];
    }

    /**
     * Get country name from country code
     */
    private function getCountryName(string $code): string
    {
        return $this->countryNames[strtoupper($code)] ?? $code;
    }

    /**
     * Initialize country names array
     */
    private function initCountryNames(): void
    {
        $this->countryNames = [
            'ID' => 'Indonesia',
            'US' => 'United States',
            'SG' => 'Singapore',
            'MY' => 'Malaysia',
            'TH' => 'Thailand',
            'PH' => 'Philippines',
            'VN' => 'Vietnam',
            'JP' => 'Japan',
            'KR' => 'South Korea',
            'CN' => 'China',
            'IN' => 'India',
            'AU' => 'Australia',
            'NZ' => 'New Zealand',
            'GB' => 'United Kingdom',
            'DE' => 'Germany',
            'FR' => 'France',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'NL' => 'Netherlands',
            'CA' => 'Canada',
            'BR' => 'Brazil',
            'MX' => 'Mexico',
            'AR' => 'Argentina',
            'RU' => 'Russia',
            'TR' => 'Turkey',
            'SA' => 'Saudi Arabia',
            'AE' => 'United Arab Emirates',
            'EG' => 'Egypt',
            'ZA' => 'South Africa',
            'NG' => 'Nigeria',
            'KE' => 'Kenya'
            // Add more countries as needed
        ];
    }

    /**
     * Export visitor data as JSON
     */
    public function toJson(): string
    {
        return json_encode($this->getVisitorDetails(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Export visitor data as array
     */
    public function toArray(): array
    {
        return $this->getVisitorDetails();
    }

    /**
     * Get summary for logging
     */
    public function getSummary(): string
    {
        $data = $this->getVisitorDetails();
        
        return sprintf(
            "[%s] %s from %s, %s using %s on %s %s",
            $data['timestamp'],
            $data['ip'],
            $data['location']['city'] ?? 'Unknown City',
            $data['location']['country'] ?? 'Unknown Country',
            $data['browser']['name'] ?? 'Unknown Browser',
            $data['device']['type'],
            $data['os']['name'] ?? 'Unknown OS'
        );
    }
}

// Example usage:
/*
// Basic usage
$visitor = new VisitorDetector();
$details = $visitor->getVisitorDetails();

// Custom server data (for testing)
$testServer = [
    'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    'REMOTE_ADDR' => '203.78.121.66',
    'HTTP_CF_IPCOUNTRY' => 'ID'
];
$visitor = new VisitorDetector($testServer);

// Get specific information
echo "Country: " . $visitor->getLocation()['country'] . "\n";
echo "Browser: " . $visitor->getBrowser()['name'] . "\n";
echo "Is Mobile: " . ($visitor->isMobile() ? 'Yes' : 'No') . "\n";
echo "Is Bot: " . ($visitor->isBot() ? 'Yes' : 'No') . "\n";

// Export as JSON
echo $visitor->toJson();

// Get summary for logging
error_log($visitor->getSummary());
*/
