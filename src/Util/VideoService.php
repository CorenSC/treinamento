<?php

namespace App\Util;

use App\Repository\VideoRepository;

class VideoService
{
    private $videoRepository;

    public function __construct(VideoRepository $videoRepository)
    {
        $this->videoRepository = $videoRepository;
    }

    public function getGroupedVideos(): array
    {
        $videos = $this->videoRepository->findAll();
        $groupedVideos = [];

        foreach ($videos as $video) {
            $typeName = $video->getTypeTraining()->getName();
            if (!isset($groupedVideos[$typeName])) {
                $groupedVideos[$typeName] = [];
            }
            $groupedVideos[$typeName][] = $video;
        }
        return $groupedVideos;
    }
}