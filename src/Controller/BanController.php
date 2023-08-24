<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Response, RedirectResponse};

class BanController extends AbstractController
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    #[Route('/ban', name: 'app.ban')]
    public function index(): Response|RedirectResponse
    {
        $user = $this->userRepository->findOneBy(
            ['username' => $this->getUser()->getUserIdentifier()]
        );

        if ($user->isBanned() === false) {
            return $this->redirectToRoute('app.post.index');
        }

        return $this->render('ban.html.twig');
    }
}
