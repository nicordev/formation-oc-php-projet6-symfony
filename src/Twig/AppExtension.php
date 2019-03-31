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

    public function buildYouTubeEmbedUrl(string $url): string
    {
        $toReplace = "watch?v=";
        $url = str_replace($toReplace, "embed/", $url);
        return $url;
    }
}