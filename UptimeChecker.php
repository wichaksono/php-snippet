<?php

class UptimeChecker 
{
    private $timeout;
    private $userAgent;
    private $followRedirects;
    private $maxRedirects;
    
    public function __construct($timeout = 10, $followRedirects = true, $maxRedirects = 5) 
    {
        $this->timeout = $timeout;
        $this->followRedirects = $followRedirects;
        $this->maxRedirects = $maxRedirects;
        $this->userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
    }
    
    /**
     * Cek uptime dan status website
     * 
     * @param string $url URL website yang akan dicek
     * @return array Response array dengan detail status
     */
    public function checkUptime($url) 
    {
        $startTime = microtime(true);
        
        // Validasi URL
        if (!$this->isValidUrl($url)) {
            return $this->createResponse(false, 'Invalid URL format', null, 0, 0, [
                'error_type' => 'invalid_url',
                'message' => 'URL format tidak valid'
            ]);
        }
        
        // Inisialisasi cURL
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_FOLLOWLOCATION => $this->followRedirects,
            CURLOPT_MAXREDIRS => $this->maxRedirects,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HEADER => false,
            CURLOPT_NOBODY => false,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_FORBID_REUSE => true
        ]);
        
        // Eksekusi request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        
        curl_close($ch);
        
        $endTime = microtime(true);
        $responseTime = round(($endTime - $startTime) * 1000, 2); // dalam milliseconds
        
        // Cek error cURL
        if ($curlErrno !== 0) {
            return $this->handleCurlError($curlErrno, $curlError, $responseTime);
        }
        
        // Analisis status HTTP
        return $this->analyzeHttpStatus($httpCode, $responseTime, $totalTime, $url);
    }
    
    /**
     * Validasi format URL
     */
    private function isValidUrl($url) 
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Handle error cURL
     */
    private function handleCurlError($errno, $error, $responseTime) 
    {
        $errorTypes = [
            CURLE_COULDNT_RESOLVE_HOST => [
                'type' => 'dns_error',
                'message' => 'Domain tidak dapat di-resolve (mungkin expired atau tidak ada)',
                'status' => 'Domain Error'
            ],
            CURLE_COULDNT_CONNECT => [
                'type' => 'connection_error',
                'message' => 'Tidak dapat terhubung ke server',
                'status' => 'Connection Failed'
            ],
            CURLE_OPERATION_TIMEDOUT => [
                'type' => 'timeout',
                'message' => 'Request timeout',
                'status' => 'Timeout'
            ],
            CURLE_SSL_CONNECT_ERROR => [
                'type' => 'ssl_error',
                'message' => 'SSL connection error',
                'status' => 'SSL Error'
            ]
        ];
        
        $errorInfo = $errorTypes[$errno] ?? [
            'type' => 'unknown_error',
            'message' => $error,
            'status' => 'Unknown Error'
        ];
        
        return $this->createResponse(false, $errorInfo['status'], null, 0, $responseTime, [
            'error_type' => $errorInfo['type'],
            'message' => $errorInfo['message'],
            'curl_error' => $error,
            'curl_errno' => $errno
        ]);
    }
    
    /**
     * Analisis status HTTP
     */
    private function analyzeHttpStatus($httpCode, $responseTime, $totalTime, $url) 
    {
        $isUp = false;
        $status = '';
        $details = [];
        
        if ($httpCode >= 200 && $httpCode < 300) {
            // Success
            $isUp = true;
            $status = 'Online';
            $details = [
                'message' => 'Website berjalan normal',
                'status_category' => 'success'
            ];
        } elseif ($httpCode >= 300 && $httpCode < 400) {
            // Redirection
            $isUp = true;
            $status = 'Redirect';
            $details = [
                'message' => 'Website melakukan redirect',
                'status_category' => 'redirect'
            ];
        } elseif ($httpCode >= 400 && $httpCode < 500) {
            // Client Error
            $isUp = false;
            $status = $this->getClientErrorStatus($httpCode);
            $details = [
                'error_type' => 'client_error',
                'message' => $this->getClientErrorMessage($httpCode),
                'status_category' => '4xx_error'
            ];
        } elseif ($httpCode >= 500) {
            // Server Error
            $isUp = false;
            $status = $this->getServerErrorStatus($httpCode);
            $details = [
                'error_type' => 'server_error',
                'message' => $this->getServerErrorMessage($httpCode),
                'status_category' => '5xx_error'
            ];
        } else {
            // Unknown
            $isUp = false;
            $status = 'Unknown Status';
            $details = [
                'error_type' => 'unknown_status',
                'message' => 'Status HTTP tidak dikenali',
                'status_category' => 'unknown'
            ];
        }
        
        return $this->createResponse($isUp, $status, $httpCode, $totalTime * 1000, $responseTime, $details);
    }
    
    /**
     * Get client error status
     */
    private function getClientErrorStatus($code) 
    {
        $statuses = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            408 => 'Request Timeout',
            410 => 'Gone',
            429 => 'Too Many Requests'
        ];
        
        return $statuses[$code] ?? "Client Error ($code)";
    }
    
    /**
     * Get client error message
     */
    private function getClientErrorMessage($code) 
    {
        $messages = [
            400 => 'Request tidak valid',
            401 => 'Akses tidak terauthorisasi',
            403 => 'Akses dilarang - mungkin website tersuspend',
            404 => 'Halaman tidak ditemukan',
            408 => 'Request timeout',
            410 => 'Konten sudah tidak tersedia',
            429 => 'Terlalu banyak request'
        ];
        
        return $messages[$code] ?? 'Error pada sisi client';
    }
    
    /**
     * Get server error status
     */
    private function getServerErrorStatus($code) 
    {
        $statuses = [
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        ];
        
        return $statuses[$code] ?? "Server Error ($code)";
    }
    
    /**
     * Get server error message
     */
    private function getServerErrorMessage($code) 
    {
        $messages = [
            500 => 'Error internal server',
            502 => 'Bad gateway - server upstream bermasalah',
            503 => 'Service tidak tersedia - mungkin maintenance atau overload',
            504 => 'Gateway timeout',
            505 => 'Versi HTTP tidak didukung'
        ];
        
        return $messages[$code] ?? 'Error pada sisi server';
    }
    
    /**
     * Create standardized response array
     */
    private function createResponse($isUp, $status, $httpCode, $totalTime, $responseTime, $details = []) 
    {
        return [
            'is_up' => $isUp,
            'status' => $status,
            'http_code' => $httpCode,
            'response_time_ms' => $responseTime,
            'total_time_ms' => round($totalTime, 2),
            'checked_at' => date('Y-m-d H:i:s'),
            'timestamp' => time(),
            'details' => $details
        ];
    }
    
    /**
     * Batch check multiple URLs
     * 
     * @param array $urls Array of URLs to check
     * @return array Results for each URL
     */
    public function batchCheck($urls) 
    {
        $results = [];
        
        foreach ($urls as $url) {
            $results[$url] = $this->checkUptime($url);
        }
        
        return $results;
    }
    
    /**
     * Set timeout
     */
    public function setTimeout($timeout) 
    {
        $this->timeout = $timeout;
        return $this;
    }
    
    /**
     * Set user agent
     */
    public function setUserAgent($userAgent) 
    {
        $this->userAgent = $userAgent;
        return $this;
    }
}

// Contoh penggunaan:
/*
$checker = new UptimeChecker();

// Cek single URL
$result = $checker->checkUptime('https://example.com');
print_r($result);

// Cek multiple URLs
$urls = [
    'https://google.com',
    'https://example.com',
    'https://nonexistentdomain12345.com'
];

$results = $checker->batchCheck($urls);
foreach ($results as $url => $result) {
    echo "URL: $url\n";
    echo "Status: " . ($result['is_up'] ? 'UP' : 'DOWN') . "\n";
    echo "Response: {$result['status']}\n";
    echo "Time: {$result['response_time_ms']}ms\n\n";
}
*/

?>
