<?php

namespace App\Twig;

use App\Util\VideoService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class VideoExtension extends AbstractExtension
{
    private $videoService;

    public function __construct(VideoService $videoService)
    {
        $this->videoService = $videoService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getGroupedVideos', [$this, 'getGroupedVideos']),
        ];
    }

    public function getGroupedVideos(): array
    {
        return $this->videoService->getGroupedVideos();
    }
}