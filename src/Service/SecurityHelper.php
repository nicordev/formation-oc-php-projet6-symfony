<?php

namespace App\Service;


class SecurityHelper
{
    /**
     * Password must have at least 8 characters, 1 lower case, 1 upper case, 1 digit, 1 special character, avoid any non-whitespace character
     *
     * @param string|null $password
     * @return bool
     */
    public static function hasStrongPassword(?string $password)
    {
        return preg_match("#^(?=.*?[a-z])(?=.*?[A-Z])(?=.*?[0-9])(?=.*?[!$%@\#£€*?&_])\S{8,}$#", $password);
    }
}
