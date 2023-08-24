<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class SettingsController extends AbstractController
{
    public function __construct(
        protected UserRepository $userRepository,
        protected User $user
    ) {}

    #[Route('/user/@{username}/settings', name: 'app.settings')]
    public function settings(string $username): RedirectResponse
    {
        return $this->redirectToRoute('app.settings.profile', [
            'username' => $username
        ]);
    }

    #[Route('/user/@{username}/settings/profile', name: 'app.settings.profile')]
    public function profile(): Response
    {
        return $this->render("settings/profile.html.twig", [
            'user_profile' => $this->user
        ]);
    }

    #[Route('/user/@{username}/settings/account', name: 'app.settings.account')]
    public function account(): Response
    {
        return $this->render("settings/account.html.twig");
    }

    #[Route('/user/@{username}/delete', name: 'app.user.delete', methods: 'POST')]
    public function deleteAccount(string $username): RedirectResponse
    {
        (new Session())->invalidate();

        $user = $this->userRepository->findOneBy(['username' => $username]);
        $this->userRepository->remove($user, true);

        return $this->redirectToRoute('app.auth.login', [
            'isAccountDeleted' => true
        ]);
    }
}
