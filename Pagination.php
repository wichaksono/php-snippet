<?php
/**
 * Pagination class for generating page navigation links.
 * Supports customization of link styles, text, and behavior.
 *
 * Example usage:
 *
 * $page = new Pagination();
 * $page->setBaseUrl('http://example.com/page/?filter=1&category=2');
 * $page->setParam('p');
 * $page->setCurrentPage(5); or $page->setCurrentPage($_GET['p'] ?? 1);
 * $page->setTotalPages(10); or $page->setTotalPagesRaw(100, 10);
 *
 * $page->setParam('p');
 *
 * echo $page->getPrevLink();
 * echo PHP_EOL;
 * echo $page->getNextLink();
 * echo PHP_EOL;
 * print_r($page->getNumbers());
 *
 * @version 1.0.0
 * @since 1.0.0
 */
final class Pagination
{
    /**
     * @var string Base URL for pagination links.
     */
    private string $baseUrl;

    /**
     * @var int Total number of pages.
     */
    private int $totalPages;

    /**
     * @var int Current active page.
     */
    private int $currentPage;

    /**
     * @var array Configuration options for pagination.
     */
    private array $args = [
        'end_size'      => 1, // Number of pages to show at start and end.
        'mid_size'      => 2, // Number of pages to show around current page.
        'base_url'      => '',
        'total_pages'   => 1,
        'current_page'  => 1,
        'link_class'    => 'page-numbers', // Default CSS class for links.
        'current_class' => 'current',      // CSS class for the current page.
        'prev_text'     => '&laquo;',      // Text for previous link.
        'next_text'     => '&raquo;',      // Text for next link.
    ];

    /**
     * @var string Query parameter name for the page number.
     */
    private string $param = 'page';

    /**
     * Constructor to initialize pagination with custom arguments.
     *
     * @param array{
     *     base_url: string,
     *     total_pages: int,
     *     current_page: int,
     *     end_size?: int,
     *     mid_size?: int,
     *     link_class?: string,
     *     current_class?: string,
     *     prev_text?: string,
     *     next_text?: string
     * } $args Custom configuration options.
     */
    public function __construct(array $args = [])
    {
        $this->args = array_merge($this->args, $args);
        $this->baseUrl = $this->args['base_url'];
        $this->totalPages = $this->args['total_pages'];
        $this->currentPage = $this->args['current_page'];
    }

    /**
     * Set the base URL for the pagination links.
     *
     * @param string $baseUrl
     */
    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Set the total number of pages.
     *
     * @param int $totalPages
     */
    public function setTotalPages(int $totalPages): void
    {
        $this->totalPages = $totalPages;
    }

    /**
     * Set the total number of pages based on the total number of rows and rows per page.
     *
     * @param int $totalRows Total number of rows.
     * @param int $rowsPerPage Number of rows per page.
     */
    public function setTotalPagesRaw(int $totalRows, int $rowsPerPage): void
    {
        $this->totalPages = ceil($totalRows / $rowsPerPage);
    }

    /**
     * Set the current active page.
     *
     * @param int $currentPage
     */
    public function setCurrentPage(int $currentPage): void
    {
        $this->currentPage = $currentPage;
    }

    /**
     * Set the number of pages to show at the start and end.
     *
     * @param int $endSize
     */
    public function setEndSize(int $endSize): void
    {
        $this->args['end_size'] = $endSize;
    }

    /**
     * Set the number of pages to show around the current page.
     *
     * @param int $midSize
     */
    public function setMidSize(int $midSize): void
    {
        $this->args['mid_size'] = $midSize;
    }

    /**
     * Set the query parameter name for the page number.
     *
     * @param string $param
     */
    public function setParam(string $param): void
    {
        $this->param = $param;
    }

    /**
     * Set the text for the "Previous" navigation link.
     *
     * @param string $text The text to display for the "Previous" link.
     */
    public function setPrevText(string $text): void
    {
        $this->args['prev_text'] = $text;
    }

    /**
     * Set the text for the "Next" navigation link.
     *
     * @param string $text The text to display for the "Next" link.
     */
    public function setNextText(string $text): void
    {
        $this->args['next_text'] = $text;
    }


    /**
     * Generate the pagination links and return them as an array.
     *
     * @return array List of pagination links or spans.
     */
    public function getNumbers(): array
    {
        $numbers = [];
        $endSize = $this->args['end_size'];
        $midSize = $this->args['mid_size'];

        // Add start pages
        for ($i = 1; $i <= min($endSize, $this->totalPages); $i++) {
            $numbers[] = $this->createLinkOrSpan($i);
        }

        // Add ellipsis if needed before the mid section
        if ($this->currentPage - $midSize > $endSize + 1) {
            $numbers[] = $this->createSpan('...');
        }

        // Add middle pages
        $midStart = max($endSize + 1, $this->currentPage - $midSize);
        $midEnd = min($this->totalPages - $endSize, $this->currentPage + $midSize);
        for ($i = $midStart; $i <= $midEnd; $i++) {
            $numbers[] = $this->createLinkOrSpan($i);
        }

        // Add ellipsis if needed after the mid section
        if ($this->currentPage + $midSize < $this->totalPages - $endSize) {
            $numbers[] = $this->createSpan('...');
        }

        // Add end pages
        $startEnd = max($this->totalPages - $endSize + 1, $midEnd + 1);
        for ($i = $startEnd; $i <= $this->totalPages; $i++) {
            $numbers[] = $this->createLinkOrSpan($i);
        }

        return $numbers;
    }

    /**
     * Generate the "Next" navigation link.
     *
     * @return string
     */
    public function getNextLink(): string
    {
        return $this->createNavLink($this->currentPage + 1, 'next', $this->args['next_text']);
    }

    /**
     * Generate the "Previous" navigation link.
     *
     * @return string
     */
    public function getPrevLink(): string
    {
        return $this->createNavLink($this->currentPage - 1, 'prev', $this->args['prev_text']);
    }

    /**
     * Create a navigation link for "Next" or "Previous".
     *
     * @param int    $page Page number.
     * @param string $direction Direction (next or prev).
     * @param string $text Text to display.
     * @return string
     */
    private function createNavLink(int $page, string $direction, string $text): string
    {
        if (($direction === 'next' && $page > $this->totalPages) || ($direction === 'prev' && $page < 1)) {
            $classes = $this->args['link_class'] . " $direction disabled";
            return '<span class="' . $classes . '">' . $text . '</span>';
        }
        return $this->createLink($page, $direction, $text);
    }

    /**
     * Create a link or span element for a page number.
     *
     * @param int $page Page number.
     * @return string
     */
    private function createLinkOrSpan(int $page): string
    {
        return $page === $this->currentPage ? $this->createSpan((string)$page) : $this->createLink($page);
    }

    /**
     * Create a link element for a page number.
     *
     * @param int    $page Page number.
     * @param string $linkClasses Additional classes for the link.
     * @param string $title Title or text for the link.
     * @return string
     */
    private function createLink(int $page, string $linkClasses = '', string $title = ''): string
    {
        $link = $this->baseUrl . (strpos($this->baseUrl, '?') ? '&' : '?') . $this->param . '=' . $page;
        $linkClasses = trim("$linkClasses {$this->args['link_class']}");
        $title = $title ?: $page;

        return '<a class="' . $linkClasses . '" href="' . $link . '">' . $title . '</a>';
    }

    /**
     * Create a span element for a page number or ellipsis.
     *
     * @param string $text Text to display inside the span.
     * @return string
     */
    private function createSpan(string $text): string
    {
        $class = $this->args['link_class'];
        if ($text === (string)$this->currentPage) {
            $class .= ' ' . $this->args['current_class'];
        }
        return '<span class="' . trim($class) . '">' . $text . '</span>';
    }
}
