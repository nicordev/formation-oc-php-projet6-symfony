<?php

namespace App\Tests\Service;

use App\Service\Paginator;
use PHPUnit\Framework\TestCase;

class PaginatorTest extends TestCase
{
    public function testFitCurrentPageInBoundaries()
    {
        $paginator = new Paginator();

        $paginator->pagesCount = 20;
        $paginator->currentPage = 21;

        $paginator->fitCurrentPageInBoundaries();
        $this->assertEquals(20, $paginator->currentPage);

        $paginator->currentPage = -1;

        $paginator->fitCurrentPageInBoundaries();
        $this->assertEquals(1, $paginator->currentPage);
    }

    public function testUpdate()
    {
        $paginator = new Paginator();

        $itemsPerPage = 10;
        $itemsCount = 100;

        $paginator->update(
            -5,
            $itemsPerPage,
            $itemsCount,
            true
        );

        $this->assertEquals(1, $paginator->currentPage);

        $paginator->update(
            1000,
            $itemsPerPage,
            $itemsCount,
            true
        );

        $this->assertEquals(10, $paginator->currentPage);
    }
}