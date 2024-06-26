<?php
declare(strict_types=1);

/**
 * Pagination Builder
 * 
 * @version 1.0.0
 * @since 1.0.0
 * @author Neon Web Developer
 */
final class PaginationBuilder
{
    private string $baseUrl;

    private int $totalItems;

    private int $itemsPerPage;

    private int $currentPage;

    private string $containerClasses = 'justify-content-end';

    private array $containerAttributes = [
        'aria-label' => 'Page navigation'
    ];

    private string $wrapClasses = '';

    private array $wrapAttributes = [];

    private string $itemClasses = '';

    private array $itemAttributes = [];

    private string $linkClasses = '';

    private array $linkAttributes = [];

    private string $activeClasses = '';

    public function __construct(string $baseUrl, int $totalItems, int $itemsPerPage, int $currentPage)
    {
        $this->baseUrl      = $baseUrl;
        $this->totalItems   = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage  = $currentPage;
    }

    public function setContainerClasses(string $containerClasses): void
    {
        $this->containerClasses = ' ' . $containerClasses;
    }

    public function setContainerAttributes(array $containerAttributes): void
    {
        $this->containerAttributes = $containerAttributes;
    }

    public function setWrapClasses(string $wrapClasses): void
    {
        $this->wrapClasses = ' ' . $wrapClasses;
    }

    public function setWrapAttributes(array $wrapAttributes): void
    {
        $this->wrapAttributes = $wrapAttributes;
    }

    public function setItemClasses(string $itemClasses): void
    {
        $this->itemClasses = ' ' . $itemClasses;
    }

    public function setItemAttributes(array $itemAttributes): void
    {
        $this->itemAttributes = $itemAttributes;
    }

    public function setLinkClasses(string $linkClasses): void
    {
        $this->linkClasses = ' ' . $linkClasses;
    }

    public function setLinkAttributes(array $linkAttributes): void
    {
        $this->linkAttributes = $linkAttributes;
    }

    public function setActiveClasses(string $activeClasses): void
    {
        $this->activeClasses = ' ' . $activeClasses;
    }

    public function render(): string
    {
        $pagination = '<nav class="d-flex' . $this->containerClasses . '"' . $this->attributes($this->containerAttributes) . '>';
        $pagination .= '<ul class="pagination' . $this->wrapClasses . '" ' . $this->attributes($this->wrapAttributes) . '>';

        $totalPages = ceil($this->totalItems / $this->itemsPerPage);

        if ($totalPages > 7) {
            if ($this->currentPage == 1) {
                $pagination .= $this->pageItemActive('1');
            } else {
                $pagination .= $this->pageItem('Previous', ($this->currentPage - 1));
                $pagination .= $this->pageItem('1', $this->currentPage);
            }

            if ($totalPages - $this->currentPage > 3) {
                if ($this->currentPage > 4) {
                    $pagination .= $this->pageItem('...', '');
                    $pagination .= $this->pageItem(($this->currentPage - 1), ($this->currentPage - 1));
                    $pagination .= $this->pageItemActive($this->currentPage);
                    $pagination .= $this->pageItem(($this->currentPage + 1), ($this->currentPage + 1));
                } else {
                    for ($page_no = 2; $page_no <= 5; $page_no++) {
                        if ($this->currentPage == $page_no) {
                            $pagination .= $this->pageItemActive($page_no);
                        } else {
                            $pagination .= $this->pageItem($page_no, $page_no);
                        }
                    }
                }
            }

            if ($totalPages - $this->currentPage < 4) {
                $pagination .= $this->pageItem('...', '');
                for ($page_no = $totalPages - 4; $page_no <= $totalPages - 1; $page_no++) {
                    if ($this->currentPage == $page_no) {
                        $pagination .= $this->pageItemActive($page_no);
                    } else {
                        $pagination .= $this->pageItem($page_no, $page_no);
                    }
                }
            } else {
                $pagination .= $this->pageItem('...', '');
            }

            if ($this->currentPage == $totalPages) {
                $pagination .= $this->pageItemActive($totalPages);
            } else {
                $pagination .= $this->pageItem($totalPages, $totalPages);
                $pagination .= $this->pageItem('Next', ($this->currentPage + 1));
            }
        } else {
            if ($this->currentPage > 1) {
                $pagination .= $this->pageItem('Previous', ($this->currentPage - 1));
            }

            for ($page_no = 1; $page_no <= $totalPages; $page_no++) {
                if ($this->currentPage == $page_no) {
                    $pagination .= $this->pageItemActive($page_no);
                } else {
                    $pagination .= $this->pageItem($page_no, $page_no);
                }
            }
            if ($this->currentPage < $totalPages) {
                $pagination .= $this->pageItem('Next', ($this->currentPage + 1));
            }
        }

        $pagination .= '</ul>';
        $pagination .= '</nav>';

        return $pagination;
    }

    private function attributes(array $attributes): string
    {
        $_attributes = '';
        foreach ($attributes as $key => $value) {
            $_attributes .= ' ' . $key . '="' . $value . '"';
        }

        return $_attributes;
    }

    private function pageItemActive($label): string
    {
        return $this->pageItem($label, '', true);
    }

    private function pageItem($label, $page = '', $isCurrentPage = false): string
    {
        $classes = ['page-item'];

        if ($this->itemClasses != '') {
            $classes[] = $this->itemClasses;
        }

        $attributes = $this->itemAttributes;
        $tagLabel   = '<span class="page-link">' . $label . '</span>';
        if ($isCurrentPage) {
            $classes[]                  = 'active';
            $classes[]                  = $this->activeClasses;
            $attributes['aria-current'] = 'page';
        }

        if ( ! $isCurrentPage || $page !== '') {
            $linkClasses = 'page-link';
            if ($this->linkClasses != '') {
                $linkClasses .= ' ' . $this->linkClasses;
            }

            $tagLabel = '<a class="' . $linkClasses . '" href="' . $this->baseUrl . $page . '"' . $this->attributes($this->linkAttributes) . '>' . $label . '</a>';
        }

        return '<li class="' . implode(' ',
                $classes) . '"' . $this->attributes($attributes) . '>' . $tagLabel . '</li>';
    }

}
