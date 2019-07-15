<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('youTubeEmbed', [$this, 'buildYouTubeEmbedUrl'])
        ];
    }

    /**
     * Change a classic youtube url to an embedded one
     *
     * @param string $url
     * @return string
     */
    public function buildYouTubeEmbedUrl(string $url): string
    {
        $count = 0;

        $toReplace = "watch?v=";
        $replaced = str_replace($toReplace, "embed/", $url, $count);

        if ($count === 1) {
            return $replaced;
        }

        $shareYouTubeUrl = "youtu.be/";

        return str_replace($shareYouTubeUrl, "www.youtube.com/embed/", $url, $count);
    }
}
