<?php

class ArticleExtractor
{
    private string $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    private int $timeout = 30;
    private array $allowedTags = ['p', 'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'em', 'i', 'strong', 'b'];

    /**
     * Ekstraksi lengkap dengan pilihan format
     * @param string $url URL target
     * @param int $maxPages Maksimal halaman yang diambil
     * @param string $format 'markdown' atau 'text'
     */
    public function getFullArticle(string $url, int $maxPages = 5, string $format = 'markdown'): array
    {
        $html = $this->fetchHtml($url);
        if (!$html) {
            return ['error' => 'Gagal mengambil konten'];
        }

        $dom = $this->createDom($html);
        $xpath = new DOMXPath($dom);
        
        // 1. Title
        $title = $this->extractTitle($xpath);
        
        // 2. Published At
        $publishedAt = $this->extractPublishedDate($xpath);

        // 3. Featured Image
        $articleNode = $this->findArticleContainer($xpath);
        $featured = ['url' => '', 'caption' => ''];
        if ($articleNode) {
            $imageData = $this->extractFeaturedImage($xpath, $articleNode, $title, $dom);
            $featured = [
                'url'     => $imageData['image'],
                'caption' => $imageData['caption']
            ];
        }

        // 4. Content Text (Markdown atau Text Biasa)
        $contentText = $this->extractContentWithPagination($url, $maxPages, $format);

        // Return sesuai format permintaan
        return [
            'content_text'   => $contentText,
            'featured_image' => $featured,
            'published_at'   => $publishedAt,
            'title'          => $title,
            'url'            => $url,
            'format_used'    => $format
        ];
    }

    /* ================= CORE LOGIC ================= */

    private function fetchHtml(string $url): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => $this->userAgent,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $html = curl_exec($ch);
        curl_close($ch);
        return $html ?: '';
    }

    private function createDom(string $html): DOMDocument
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        return $dom;
    }

    private function extractContentWithPagination(string $baseUrl, int $maxPages, string $format): string
    {
        $visited = [];
        $queue = [$baseUrl];
        $seenBlocks = [];
        $finalContent = [];

        while ($queue && count($visited) < $maxPages) {
            $url = array_shift($queue);
            if (isset($visited[$url])) continue;
            $visited[$url] = true;

            $html = $this->fetchHtml($url);
            if (!$html) continue;

            $dom = $this->createDom($html);
            $xpath = new DOMXPath($dom);
            $container = $this->findArticleContainer($xpath);

            if ($container) {
                $this->cleanContainer($container);
                foreach ($container->getElementsByTagName('*') as $el) {
                    if (in_array(strtolower($el->nodeName), $this->allowedTags)) {
                        
                        // Cek format yang diinginkan: markdown atau text biasa
                        $output = ($format === 'text') 
                            ? $this->nodeToText($el) 
                            : $this->nodeToMarkdown($el);

                        $output = trim($output);
                        
                        if ($output !== '') {
                            $hash = md5($output);
                            if (!isset($seenBlocks[$hash])) {
                                $seenBlocks[$hash] = true;
                                $finalContent[] = $output;
                            }
                        }
                    }
                }
            }

            foreach ($this->detectPaginationLinks($dom, $baseUrl) as $nextUrl) {
                if (!isset($visited[$nextUrl])) $queue[] = $nextUrl;
            }
        }

        return implode("\n\n", $finalContent);
    }

    /* ================= CONVERSION HELPERS ================= */

    private function nodeToMarkdown(DOMNode $node): string
    {
        if ($node->nodeType === XML_TEXT_NODE) return $this->normalizeText($node->nodeValue);
        if ($node->nodeType !== XML_ELEMENT_NODE) return '';

        $tag = strtolower($node->nodeName);
        $content = '';
        foreach ($node->childNodes as $child) {
            $content .= $this->nodeToMarkdown($child);
        }

        $content = trim($content);
        if ($content === '' || $this->isNoiseText($content)) return '';

        return match ($tag) {
            'p'           => "$content\n\n",
            'h2'          => "\n## $content\n\n",
            'h3'          => "\n### $content\n\n",
            'li'          => "- $content\n",
            'em', 'i'     => "*$content*",
            'strong', 'b' => "**$content**",
            default       => $content
        };
    }

    private function nodeToText(DOMNode $node): string
    {
        // Ambil text content saja, abaikan semua tag formatting
        $text = $this->normalizeText($node->textContent);
        if ($text === '' || $this->isNoiseText($text)) return '';
        
        // Jika elemen adalah block-level, pastikan ada pemisah nantinya (implode di atas akan menangani newline)
        return $text;
    }

    /* ================= EXTRACTION HELPERS ================= */

    private function extractPublishedDate(DOMXPath $xp): string
    {
        $queries = [
            '//meta[@property="article:published_time"]/@content',
            '//meta[@name="pubdate"]/@content',
            '//meta[@name="publishdate"]/@content',
            '//meta[@property="og:updated_time"]/@content',
            '//script[@type="application/ld+json"]'
        ];

        foreach ($queries as $q) {
            $nodes = $xp->query($q);
            if ($nodes->length > 0) {
                $val = $nodes->item(0)->nodeValue;
                if (str_contains($q, 'application/ld+json')) {
                    $json = json_decode($val, true);
                    if (isset($json['datePublished'])) return $json['datePublished'];
                    if (isset($json['dateCreated'])) return $json['dateCreated'];
                    continue;
                }
                if ($val) return $val;
            }
        }
        return '';
    }

    private function extractTitle(DOMXPath $xp): string
    {
        $h1 = $xp->query('//h1')->item(0);
        return $h1 ? $this->normalizeText($h1->textContent) : '';
    }

    private function extractFeaturedImage(DOMXPath $xp, DOMNode $article, string $title, DOMDocument $dom): array
    {
        $imgs = $article->getElementsByTagName('img');
        foreach ($imgs as $img) {
            $src = $img->getAttribute('src');
            if (!$src || str_starts_with($src, 'data:') || str_contains($src, 'base64')) continue;

            $caption = '';
            $parent = $img->parentNode;
            while ($parent && $parent->nodeName !== 'body') {
                if ($parent->nodeName === 'figure') {
                    $figcap = (new DOMXPath($dom))->query('.//figcaption', $parent)->item(0);
                    if ($figcap) $caption = $this->normalizeText($figcap->textContent);
                    break;
                }
                $parent = $parent->parentNode;
            }

            if (!$caption) $caption = $this->normalizeText($img->getAttribute('alt'));

            return [
                'image'   => $src,
                'caption' => $caption ?: $title
            ];
        }

        $og = $xp->query('//meta[@property="og:image"]/@content')->item(0);
        return [
            'image'   => $og ? $og->nodeValue : '',
            'caption' => $title
        ];
    }

    /* ================= DOM & TEXT UTILS ================= */

    private function findArticleContainer(DOMXPath $xp): ?DOMNode
    {
        $best = null;
        $bestScore = 0;
        $candidates = $xp->query('//article|//section|//div');

        foreach ($candidates as $n) {
            $len = mb_strlen(trim($n->textContent));
            $pCount = $xp->query('.//p', $n)->length;
            if ($len < 500 || $pCount < 3) continue;

            $aCount = $xp->query('.//a', $n)->length;
            $score = $len + ($pCount * 200) - ($aCount * 150);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $n;
            }
        }
        return $best;
    }

    private function cleanContainer(DOMNode $c): void
    {
        $tagsToRemove = ['nav', 'aside', 'footer', 'form', 'button', 'script', 'style'];
        foreach ($tagsToRemove as $t) {
            $nodes = $c->ownerDocument->getElementsByTagName($t);
            for ($i = $nodes->length - 1; $i >= 0; $i--) {
                $node = $nodes->item($i);
                if ($node && $node->parentNode) {
                    $node->parentNode->removeChild($node);
                }
            }
        }
    }

    private function normalizeText(string $t): string
    {
        $t = html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', $t));
    }

    private function isNoiseText(string $text): bool
    {
        $patterns = ['/\b(baca|lihat|simak)\s+juga\b/i', '/^baca\s*:/i', '/^iklan\b/i'];
        foreach ($patterns as $p) {
            if (preg_match($p, $text)) return true;
        }
        return false;
    }

    private function detectPaginationLinks(DOMDocument $dom, string $baseUrl): array
    {
        $links = [];
        foreach ($dom->getElementsByTagName('a') as $a) {
            $href = trim($a->getAttribute('href'));
            $text = strtolower(trim($a->textContent));
            if ($href && preg_match('/^(next|selanjutnya|›|\d+)$/', $text)) {
                $links[] = $this->resolveUrl($baseUrl, $href);
            }
        }
        return array_unique($links);
    }

    private function resolveUrl(string $base, string $rel): string
    {
        if (parse_url($rel, PHP_URL_SCHEME)) return $rel;
        $parts = parse_url($base);
        return $parts['scheme'] . '://' . $parts['host'] . '/' . ltrim($rel, '/');
    }
}

/* ================= EXECUTION ================= */

if ( isset($_GET['url']) ) {
    $targetUrl = $_GET['url'];
    $extractor = new ArticleExtractor();
    
    // Mode Markdown (Default)
    // $result = $extractor->getFullArticle($targetUrl, 5, 'markdown');

    // Mode Text Biasa
    $result = $extractor->getFullArticle($targetUrl, 5, 'text');

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
} else {
    // Ganti dengan URL target yang diinginkan
    echo "Please provide a URL parameter, e.g., ?url=https://example.com/article" ;
}

