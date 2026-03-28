<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Input\PhotoFiltersInput;
use App\Entity\User;
use App\Likes\LikeRepository;
use App\Repository\PhotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        PhotoRepository $photoRepository,
        LikeRepository $likeRepository,
    ): Response {
        $filters = new PhotoFiltersInput(
            trim((string) $request->query->get('location', '')),
            trim((string) $request->query->get('camera', '')),
            trim((string) $request->query->get('description', '')),
            trim((string) $request->query->get('taken_at', '')),
            trim((string) $request->query->get('username', '')),
        );

        $photos = $photoRepository->findAllWithUsersByFilters($filters);

        $userId = $request->getSession()->get('user_id');
        $currentUser = null;
        $userLikes = [];

        if ($userId) {
            $currentUser = $em->getRepository(User::class)->find($userId);

            if ($currentUser instanceof User) {
                $likeRepository->setUser($currentUser);

                foreach ($photos as $photo) {
                    $photoId = $photo->getId();

                    if ($photoId === null) {
                        continue;
                    }

                    $userLikes[$photoId] = $likeRepository->hasUserLikedPhoto($photo);
                }
            }
        }

        return $this->render('home/index.html.twig', [
            'photos' => $photos,
            'currentUser' => $currentUser,
            'userLikes' => $userLikes,
            'filters' => $filters,
        ]);
    }
}
