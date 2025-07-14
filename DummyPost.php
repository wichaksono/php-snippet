<?php

class DummyPost
{
    private array $loremWords = [
        'lorem',
        'ipsum',
        'dolor',
        'sit',
        'amet',
        'consectetur',
        'adipiscing',
        'elit',
        'sed',
        'do',
        'eiusmod',
        'tempor',
        'incididunt',
        'ut',
        'labore',
        'et',
        'dolore',
        'magna',
        'aliqua',
        'enim',
        'ad',
        'minim',
        'veniam',
        'quis',
        'nostrud',
        'exercitation',
        'ullamco',
        'laboris',
        'nisi',
        'aliquip',
        'ex',
        'ea',
        'commodo',
        'consequat',
        'duis',
        'aute',
        'irure',
        'reprehenderit',
        'in',
        'voluptate',
        'velit',
        'esse',
        'cillum',
        'fugiat',
        'nulla',
        'pariatur',
        'excepteur',
        'sint',
        'occaecat',
        'cupidatat',
        'non',
        'proident',
        'sunt',
        'culpa',
        'qui',
        'officia',
        'deserunt',
        'mollit',
        'anim',
        'id',
        'est',
        'laborum'
    ];

    private int $currentHeadingLevel = 2;

    public function generate($wordCount = 500): string
    {
        $html                      = '';
        $remainingWords            = $wordCount;
        $this->currentHeadingLevel = 2; // Reset heading level

        // Main heading
        $html           .= $this->generateHeading(2, 3, 8);
        $remainingWords -= 5;

        // Intro paragraph
        $html           .= $this->generateParagraph(30, 50);
        $remainingWords -= 40;

        // Image
        $html .= $this->generateImage();

        // Mix content
        while ($remainingWords > 50) {
            $contentType = rand(1, 10);

            switch ($contentType) {
                case 1:
                    $html           .= $this->generateHierarchicalHeading();
                    $remainingWords -= 4;
                    break;
                case 2:
                    $html           .= $this->generateList('ul', rand(3, 6));
                    $remainingWords -= 20;
                    break;
                case 3:
                    $html           .= $this->generateList('ol', rand(3, 5));
                    $remainingWords -= 20;
                    break;
                case 4:
                    $html           .= $this->generateTable(rand(3, 5), rand(3, 4));
                    $remainingWords -= 30;
                    break;
                case 5:
                    $html           .= $this->generateBlockquote(15, 25);
                    $remainingWords -= 20;
                    break;
                case 6:
                    $html           .= $this->generateCodeBlock();
                    $remainingWords -= 10;
                    break;
                case 7:
                    $html .= $this->generateImage();
                    break;
                default:
                    $html           .= $this->generateParagraph(25, 60);
                    $remainingWords -= 40;
                    break;
            }
        }

        // Final paragraph
        if ($remainingWords > 0) {
            $html .= $this->generateParagraph($remainingWords, $remainingWords);
        }

        return $html;
    }

    private function generateWords($count): string
    {
        $words = [];
        for ($i = 0; $i < $count; $i++) {
            $words[] = $this->loremWords[array_rand($this->loremWords)];
        }
        return implode(' ', $words);
    }

    private function generateHeading($level, $minWords, $maxWords): string
    {
        $words = $this->generateWords(rand($minWords, $maxWords));
        return "<h{$level}>" . ucfirst($words) . "</h{$level}>\n\n";
    }

    private function generateHierarchicalHeading(): string
    {
        // Random decision to go deeper, stay same, or go back up
        $action = rand(1, 10);

        if ($action <= 4 && $this->currentHeadingLevel < 6) {
            // Go deeper (40% chance)
            $this->currentHeadingLevel++;
        } elseif ($action <= 6 && $this->currentHeadingLevel > 2) {
            // Go back up (20% chance)
            $this->currentHeadingLevel--;
        }
        // Stay same level (40% chance)

        return $this->generateHeading($this->currentHeadingLevel, 2, 6);
    }

    private function generateParagraph($minWords, $maxWords): string
    {
        $words = $this->generateWords(rand($minWords, $maxWords));

        // Add some links randomly
        $wordsArray = explode(' ', $words);
        $linkCount  = rand(0, 2);

        for ($i = 0; $i < $linkCount; $i++) {
            $randomIndex              = rand(0, count($wordsArray) - 1);
            $wordsArray[$randomIndex] = '<a href="#" target="_blank">' . $wordsArray[$randomIndex] . '</a>';
        }

        // Add some inline code randomly
        if (rand(1, 3) == 1) {
            $randomIndex              = rand(0, count($wordsArray) - 1);
            $wordsArray[$randomIndex] = '<code>' . $wordsArray[$randomIndex] . '</code>';
        }

        return '<p>' . ucfirst(implode(' ', $wordsArray)) . '.</p>' . "\n\n";
    }

    private function generateImage(): string
    {
        $width  = rand(400, 800);
        $height = rand(200, 400);
        $alt    = $this->generateWords(rand(3, 6));

        return '<img src="https://placehold.co/' . $width . 'x' . $height . '" alt="' . $alt . '" />' . "\n\n";
    }

    private function generateList($type, $items): string
    {
        $html = "<{$type}>\n";
        for ($i = 0; $i < $items; $i++) {
            $html .= '  <li>' . ucfirst($this->generateWords(rand(3, 8))) . '</li>' . "\n";
        }
        $html .= "</{$type}>\n\n";
        return $html;
    }

    private function generateTable($rows, $cols): string
    {
        $html = "<table>\n";

        // Header
        $html .= "  <thead>\n    <tr>\n";
        for ($i = 0; $i < $cols; $i++) {
            $html .= '      <th>' . ucfirst($this->generateWords(rand(1, 3))) . '</th>' . "\n";
        }
        $html .= "    </tr>\n  </thead>\n";

        // Body
        $html .= "  <tbody>\n";
        for ($i = 0; $i < $rows; $i++) {
            $html .= "    <tr>\n";
            for ($j = 0; $j < $cols; $j++) {
                $html .= '      <td>' . ucfirst($this->generateWords(rand(1, 4))) . '</td>' . "\n";
            }
            $html .= "    </tr>\n";
        }
        $html .= "  </tbody>\n</table>\n\n";

        return $html;
    }

    private function generateBlockquote($minWords, $maxWords): string
    {
        $quote = $this->generateWords(rand($minWords, $maxWords));
        return '<blockquote>' . ucfirst($quote) . '.</blockquote>' . "\n\n";
    }

    private function generateCodeBlock(): string
    {
        $codeLines = [
            'function generateCode() {',
            '  const data = fetchData();',
            '  return processData(data);',
            '}'
        ];

        return '<pre><code>' . implode("\n", $codeLines) . '</code></pre>' . "\n\n";
    }
}

// Usage example:
// $dummyPost = new DummyPost();
// echo $dummyPost->generate(600); // Generate post with ~600 words
