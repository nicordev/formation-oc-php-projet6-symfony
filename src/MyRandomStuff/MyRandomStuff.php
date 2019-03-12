<?php

namespace MyRandomStuff;

class MyRandomStuff
{
    private function __construct()
    {
        
    }

    /**
     * Generate a random string
     *
     * @param string|null $text
     * @param int $quantity
     * @return string
     */
    public static function randomString(string $text = '', int $quantity = 100): string
    {
        $randomString = '';
        if (!empty($text)) {
            for ($i = 0; $i < $quantity; $i++) {
                $randomString .= $text;
            }
        } else {
            for ($i = 0; $i < $quantity; $i++) {
                $randomString .= chr(rand(33, 126));
            }
        }
        return $randomString;
    }
}