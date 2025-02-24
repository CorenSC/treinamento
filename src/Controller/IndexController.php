<?php

namespace App\Controller;

use App\Repository\VideoRepository;
use App\Util\LdapService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    private VideoRepository $videoRepository;

    /**
     * @param VideoRepository $videoRepository
     */
    public function __construct(VideoRepository $videoRepository)
    {
        $this->videoRepository = $videoRepository;
    }

    #[Route('/', name: 'home')]
    public function home() {
        $videos = $this->videoRepository->findAll();

        return $this->render('inicial/inicial.html.twig', [
            'videos' => $videos
        ]);
    }
}