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
        $toReplace = "watch?v=";

        return str_replace($toReplace, "embed/", $url);
    }
}