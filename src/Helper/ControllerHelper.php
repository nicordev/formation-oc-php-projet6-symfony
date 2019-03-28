<?php

namespace App\Helper;


class ControllerHelper
{
    /**
     * Calculate the offset for paging
     *
     * @param int $page
     * @param int $linesPerPage
     * @return float|int
     */
    public static function getPagingOffset(int $page, int $linesPerPage)
    {
        return ($page - 1) * $linesPerPage;
    }
}