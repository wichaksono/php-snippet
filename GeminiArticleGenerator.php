<?php

class GeminiArticleGenerator
{
    private $apiKey;
    private $apiUrl;
    
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent';
    }
    
    /**
     * Generate artikel dengan Gemini AI
     * 
     * @param string $keyword - Keyword utama untuk artikel
     * @param string $title - Judul artikel
     * @param int $wordCount - Jumlah kata yang diinginkan
     * @param string $style - Gaya bahasa (formal, casual, persuasif, informatif, dll)
     * @param array $options - Opsi tambahan
     * @return array
     */
    public function generateArticle($keyword, $title, $wordCount = 1000, $style = 'informatif', $options = [])
    {
        try {
            // Validasi input
            $this->validateInputs($keyword, $title, $wordCount, $style);
            
            // Buat prompt yang optimal
            $prompt = $this->buildOptimalPrompt($keyword, $title, $wordCount, $style, $options);
            
            // Kirim request ke Gemini API
            $response = $this->makeApiRequest($prompt);
            
            // Parse dan format response
            return $this->formatResponse($response, $keyword, $title);
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Validasi input parameters
     */
    private function validateInputs($keyword, $title, $wordCount, $style)
    {
        if (empty($keyword)) {
            throw new Exception('Keyword tidak boleh kosong');
        }
        
        if (empty($title)) {
            throw new Exception('Judul tidak boleh kosong');
        }
        
        if ($wordCount < 100 || $wordCount > 5000) {
            throw new Exception('Panjang kata harus antara 100-5000 kata');
        }
        
        $allowedStyles = ['formal', 'casual', 'persuasif', 'informatif', 'akademik', 'jurnalistik', 'conversational'];
        if (!in_array(strtolower($style), $allowedStyles)) {
            throw new Exception('Gaya bahasa tidak valid. Pilihan: ' . implode(', ', $allowedStyles));
        }
    }
    
    /**
     * Buat prompt yang optimal untuk Gemini
     */
    private function buildOptimalPrompt($keyword, $title, $wordCount, $style, $options)
    {
        $seoGuidelines = isset($options['seo']) && $options['seo'] ? $this->getSeoGuidelines($keyword) : '';
        $targetAudience = isset($options['audience']) ? $options['audience'] : 'umum';
        $includeImages = isset($options['include_images']) && $options['include_images'];
        $tone = isset($options['tone']) ? $options['tone'] : 'profesional';
        
        $prompt = "Buatlah artikel yang sangat berkualitas dengan detail berikut:
  
  **INFORMASI ARTIKEL:**
  - Judul: {$title}
  - Keyword utama: {$keyword}
  - Target panjang: {$wordCount} kata
  - Gaya bahasa: {$style}
  - Target audience: {$targetAudience}
  - Tone: {$tone}
  
  **PERSYARATAN KONTEN:**
  1. Artikel harus original, engaging, dan memberikan value tinggi kepada pembaca
  2. Gunakan struktur yang jelas dengan heading dan subheading
  3. Integrasikan keyword '{$keyword}' secara natural (density 1-2%)
  4. Pastikan artikel mudah dibaca dan informatif
  5. Gunakan gaya bahasa {$style} yang konsisten
  6. Tambahkan call-to-action yang relevan di akhir artikel
  
  **STRUKTUR YANG DIINGINKAN:**
  - Pendahuluan yang menarik (hook)
  - Body dengan 3-5 poin utama
  - Kesimpulan yang kuat
  - FAQ (jika relevan)
  
  {$seoGuidelines}
  
  " . ($includeImages ? "**SARAN GAMBAR:**
  Berikan saran 3-5 gambar yang relevan dengan deskripsi untuk setiap section utama.
  
  " : "") . "**FORMAT OUTPUT:**
  Berikan artikel dalam format markdown dengan heading yang jelas. Pastikan konten original dan tidak copy-paste dari sumber manapun.
  
  Mulai menulis artikel sekarang:";
  
        return $prompt;
    }
    
    /**
     * Guidelines SEO tambahan
     */
    private function getSeoGuidelines($keyword)
    {
        return "
  **SEO OPTIMIZATION:**
  - Gunakan keyword '{$keyword}' dalam judul, meta description, dan heading
  - Variasikan dengan LSI keywords yang relevan
  - Buat internal linking opportunities
  - Struktur heading hierarkis (H1, H2, H3)
  - Optimasi untuk featured snippets
  - Pastikan readability score tinggi
  
  ";
    }
    
    /**
     * Kirim request ke Gemini API
     */
    private function makeApiRequest($prompt)
    {
        $headers = [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $this->apiKey
        ];
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.9,
                'maxOutputTokens' => 4096,
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ]
            ]
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl . '?key=' . $this->apiKey,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('CURL Error: ' . $error);
        }
        
        if ($httpCode !== 200) {
            throw new Exception('API Error: HTTP ' . $httpCode . ' - ' . $response);
        }
        
        $decodedResponse = json_decode($response, true);
        
        if (!$decodedResponse || !isset($decodedResponse['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception('Invalid API response format');
        }
        
        return $decodedResponse;
    }
    
    /**
     * Format response dari API
     */
    private function formatResponse($response, $keyword, $title)
    {
        $content = $response['candidates'][0]['content']['parts'][0]['text'];
        
        // Hitung statistik artikel
        $wordCount = str_word_count(strip_tags($content));
        $readingTime = ceil($wordCount / 200); // Asumsi 200 kata per menit
        $keywordDensity = $this->calculateKeywordDensity($content, $keyword);
        
        return [
            'success' => true,
            'data' => [
                'title' => $title,
                'content' => $content,
                'keyword' => $keyword,
                'statistics' => [
                    'word_count' => $wordCount,
                    'reading_time_minutes' => $readingTime,
                    'keyword_density' => $keywordDensity,
                    'character_count' => strlen($content)
                ],
                'generated_at' => date('Y-m-d H:i:s'),
                'seo_score' => $this->calculateSeoScore($content, $keyword, $title)
            ],
            'error' => null
        ];
    }
    
    /**
     * Hitung keyword density
     */
    private function calculateKeywordDensity($content, $keyword)
    {
        $text = strtolower(strip_tags($content));
        $keyword = strtolower($keyword);
        $totalWords = str_word_count($text);
        $keywordCount = substr_count($text, $keyword);
        
        return $totalWords > 0 ? round(($keywordCount / $totalWords) * 100, 2) : 0;
    }
    
    /**
     * Hitung skor SEO sederhana
     */
    private function calculateSeoScore($content, $keyword, $title)
    {
        $score = 0;
        $maxScore = 100;
        
        // Cek keyword di title (20 poin)
        if (stripos($title, $keyword) !== false) {
            $score += 20;
        }
        
        // Cek panjang konten (20 poin)
        $wordCount = str_word_count(strip_tags($content));
        if ($wordCount >= 300) {
            $score += 20;
        } elseif ($wordCount >= 150) {
            $score += 10;
        }
        
        // Cek keyword density (20 poin)
        $density = $this->calculateKeywordDensity($content, $keyword);
        if ($density >= 1 && $density <= 3) {
            $score += 20;
        } elseif ($density > 0 && $density < 1) {
            $score += 10;
        }
        
        // Cek struktur heading (20 poin)
        if (preg_match_all('/#+ /', $content) >= 3) {
            $score += 20;
        }
        
        // Cek readability (20 poin)
        $sentences = preg_split('/[.!?]+/', strip_tags($content));
        $avgWordsPerSentence = $wordCount / max(count($sentences), 1);
        if ($avgWordsPerSentence <= 20) {
            $score += 20;
        } elseif ($avgWordsPerSentence <= 25) {
            $score += 10;
        }
        
        return min($score, $maxScore);
    }
    
    /**
     * Generate multiple artikel variations
     */
    public function generateVariations($keyword, $title, $wordCount, $style, $variations = 3)
    {
        $results = [];
        
        for ($i = 0; $i < $variations; $i++) {
            $variation = $this->generateArticle(
                $keyword, 
                $title, 
                $wordCount, 
                $style, 
                ['variation' => $i + 1]
            );
            
            if ($variation['success']) {
                $results[] = $variation['data'];
            }
        }
        
        return [
            'success' => true,
            'variations_count' => count($results),
            'data' => $results
        ];
    }
}

/**
 * Cara penggunaan
 */
// Inisialisasi
$generator = new GeminiArticleGenerator('YOUR_GEMINI_API_KEY');

// Generate artikel
$result = $generator->generateArticle(
    'digital marketing',           // keyword
    'Panduan Lengkap Digital Marketing 2024', // judul
    1500,                         // panjang kata
    'informatif',                 // gaya bahasa
    [                            // opsi tambahan
        'seo' => true,
        'audience' => 'bisnis owner',
        'include_images' => true,
        'tone' => 'profesional'
    ]
);

if ($result['success']) {
    echo $result['data']['content'];
    echo "Word count: " . $result['data']['statistics']['word_count'];
    echo "SEO Score: " . $result['data']['seo_score'];
}
