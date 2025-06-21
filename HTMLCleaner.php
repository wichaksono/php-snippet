<?php
/**
 * HTML Tag Cleaner Script
 * Membersihkan tag HTML dan atribut dari konten
 */

class HTMLCleaner {
    
    /**
     * Membersihkan HTML dari tag dan atribut yang tidak diinginkan
     * 
     * @param string $html Konten HTML yang akan dibersihkan
     * @param array $allowedTags Tag yang diizinkan (opsional)
     * @return string Konten yang sudah dibersihkan
     */
    public function cleanHTML($html, $allowedTags = []) {
        // Hapus tag article, div, span, hr dan semua atributnya
        $tagsToRemove = ['article', 'div', 'span', 'hr', 'button'];
        
        foreach ($tagsToRemove as $tag) {
            // Hapus opening tag dengan semua atribut
            $html = preg_replace('/<' . $tag . '[^>]*>/i', '', $html);
            // Hapus closing tag
            $html = preg_replace('/<\/' . $tag . '>/i', '', $html);
        }
        
        // Hapus semua atribut dari tag yang tersisa
        $html = $this->removeAllAttributes($html);
        
        // Bersihkan whitespace berlebih
        $html = $this->cleanWhitespace($html);
        
        // Jika ada tag yang diizinkan, filter hanya tag tersebut
        if (!empty($allowedTags)) {
            $html = strip_tags($html, '<' . implode('><', $allowedTags) . '>');
        }
        
        return trim($html);
    }
    
    /**
     * Menghapus semua atribut dari tag HTML
     * 
     * @param string $html
     * @return string
     */
    private function removeAllAttributes($html) {
        // Pattern untuk mencocokkan tag dengan atribut
        $pattern = '/<([a-zA-Z][a-zA-Z0-9]*)\b[^>]*>/';
        
        // Replace dengan tag tanpa atribut
        $html = preg_replace($pattern, '<$1>', $html);
        
        return $html;
    }
    
    /**
     * Membersihkan whitespace berlebih
     * 
     * @param string $html
     * @return string
     */
    private function cleanWhitespace($html) {
        // Hapus baris kosong berlebih
        $html = preg_replace('/\n\s*\n/', "\n\n", $html);
        
        // Hapus spasi di awal dan akhir baris
        $html = preg_replace('/[ \t]+$/m', '', $html);
        $html = preg_replace('/^[ \t]+/m', '', $html);
        
        // Hapus spasi berlebih
        $html = preg_replace('/[ \t]+/', ' ', $html);
        
        return $html;
    }
    
    /**
     * Konversi HTML ke teks murni dengan mempertahankan struktur
     * 
     * @param string $html
     * @return string
     */
    public function htmlToText($html) {
        // Bersihkan HTML terlebih dahulu
        $html = $this->cleanHTML($html, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'ol', 'ul', 'li', 'strong', 'em']);
        
        // Konversi tag ke format teks
        $html = str_replace(['<h1>', '</h1>'], ['# ', "\n"], $html);
        $html = str_replace(['<h2>', '</h2>'], ['## ', "\n"], $html);
        $html = str_replace(['<h3>', '</h3>'], ['### ', "\n"], $html);
        $html = str_replace(['<h4>', '</h4>'], ['#### ', "\n"], $html);
        $html = str_replace(['<h5>', '</h5>'], ['##### ', "\n"], $html);
        $html = str_replace(['<h6>', '</h6>'], ['###### ', "\n"], $html);
        
        $html = str_replace(['<p>', '</p>'], ['', "\n\n"], $html);
        $html = str_replace(['<strong>', '</strong>'], ['**', '**'], $html);
        $html = str_replace(['<em>', '</em>'], ['*', '*'], $html);
        
        $html = str_replace(['<ol>', '</ol>', '<ul>', '</ul>'], ['', "\n", '', "\n"], $html);
        $html = str_replace(['<li>', '</li>'], ['- ', "\n"], $html);
        
        // Bersihkan whitespace lagi
        $html = $this->cleanWhitespace($html);
        
        return trim($html);
    }
    
    /**
     * Membaca file dan membersihkan kontennya
     * 
     * @param string $filename Nama file yang akan dibaca
     * @param string $outputFile Nama file output (opsional)
     * @return string Konten yang sudah dibersihkan
     */
    public function cleanFile($filename, $outputFile = null) {
        if (!file_exists($filename)) {
            throw new Exception("File tidak ditemukan: " . $filename);
        }
        
        $content = file_get_contents($filename);
        $cleanedContent = $this->htmlToText($content);
        
        if ($outputFile) {
            file_put_contents($outputFile, $cleanedContent);
            echo "File berhasil dibersihkan dan disimpan ke: " . $outputFile . "\n";
        }
        
        return $cleanedContent;
    }
}

$cleaner = new HTMLCleaner();
