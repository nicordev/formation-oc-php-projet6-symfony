<?php

namespace App\Service;


class Paginator
{
    public $currentPage;
    public $itemsPerPage;
    public $itemsCount;
    public $pagesCount;
    public $pagingOffset;

    /**
     * Paginator constructor.
     *
     * @param int|null $currentPage
     * @param int|null $itemsPerPage
     * @param int|null $itemsCount
     * @param bool $fitCurrentPageInBoundaries
     */
    public function __construct(
        ?int $currentPage = null,
        ?int $itemsPerPage = null,
        ?int $itemsCount = null,
        bool $fitCurrentPageInBoundaries = true
    )
    {
        $this->currentPage = $currentPage;
        $this->itemsPerPage = $itemsPerPage;
        $this->itemsCount = $itemsCount;
        if ($itemsCount && $itemsPerPage) {
            $this->pagesCount = self::countPages($itemsCount, $itemsPerPage);
            if ($fitCurrentPageInBoundaries) {
                $this->fitCurrentPageInBoundaries();
            }
        }
        if ($currentPage && $itemsPerPage) {
            $this->pagingOffset = self::calculatePagingOffset($currentPage, $itemsPerPage);
        }
    }

    /**
     * Update the attributes all at once
     *
     * @param int|null $currentPage
     * @param int|null $itemsPerPage
     * @param int|null $itemsCount
     * @param bool $fitCurrentPageInBoundaries
     */
    public function update(
        ?int $currentPage = null,
        ?int $itemsPerPage = null,
        ?int $itemsCount = null,
        bool $fitCurrentPageInBoundaries = true
    )
    {
        $this->currentPage = $currentPage;
        $this->itemsPerPage = $itemsPerPage;
        $this->itemsCount = $itemsCount;
        if ($itemsCount && $itemsPerPage) {
            $this->pagesCount = self::countPages($itemsCount, $itemsPerPage);
            if ($fitCurrentPageInBoundaries) {
                $this->fitCurrentPageInBoundaries();
            }
        }
        if ($currentPage && $itemsPerPage) {
            $this->pagingOffset = self::calculatePagingOffset($this->currentPage, $itemsPerPage); // Use of the object's attribute to stay in boundaries
        }
    }

    /**
     * Set the current page within paging count
     */
    public function fitCurrentPageInBoundaries()
    {
        if ($this->currentPage < 1) {
            $this->currentPage = 1;
        } elseif ($this->currentPage > $this->pagesCount) {
            $this->currentPage = $this->pagesCount;
        }
    }

    // Static

    /**
     * Calculate the offset for paging
     *
     * @param int $page
     * @param int $linesPerPage
     * @return float|int
     */
    public static function calculatePagingOffset(int $page, int $linesPerPage): int
    {
        return ($page - 1) * $linesPerPage;
    }

    /**
     * Count pages
     *
     * @param int $itemsCount
     * @param int $itemsPerPage
     * @return int
     */
    public static function countPages(int $itemsCount, int $itemsPerPage): int
    {
        return ceil($itemsCount / $itemsPerPage);
    }
}