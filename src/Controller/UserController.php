<?php

namespace App\Controller;

use App\Entity\{Follower, User};
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\{FollowerRepository, UserRepository};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{RedirectResponse, Request, Response};
use Symfony\Component\HttpKernel\Exception\{BadRequestHttpException, NotFoundHttpException};

#[IsGranted('IS_AUTHENTICATED')]
class UserController extends AbstractController
{
    public function __construct(
        protected FollowerRepository $followerRepository,
        protected UserRepository $userRepository
    ) {}

    #[Route('/user/@{username}', name: 'app.user.index')]
    public function index(string $username, PaginatorInterface $paginator, Request $request): NotFoundHttpException|Response
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);

        $follower = $this->followerRepository->findOneBy([
            'follower' => $user->getId(),
            'followedUser' => $this->getUser()
        ]);

        $paginationPosts = $paginator->paginate(
            $user->getPosts(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('user/index.html.twig', [
            'user_profile' => $user,
            'has_followed' => $follower,
            'pagination_posts' => $paginationPosts
        ]);
    }

    #[Route('/user/@{username}/follow', name: 'app.user.follow')]
    #[IsGranted('IS_AUTHENTICATED')]
    public function follow(string $username): RedirectResponse
    {
        $flwer = $this->followerRepository->findOneBy([
            'follower' => $this->userRepository->findOneBy(['username' => $username])->getId(),
            'followedUser' => $this->getUser()
        ]);

        if ($flwer) {
            return throw new BadRequestHttpException("You have already followed $username");
        }

        if ($username == $this->getUser()->getUserIdentifier()) {
            return throw new BadRequestHttpException("You can't follow to yourself");
        }

        $follower = new Follower();

        $follower
            ->setFollower($this->userRepository->findOneBy(['username' => $username]))
            ->setFollowedUser($this->getUser());

        $this->followerRepository->save($follower, true);

        return $this->redirectToRoute('app.user.index', [
            'username' => $username
        ]);
    }

    #[Route('/user/@{username}/unfollow', name: 'app.user.unfollow')]
    #[IsGranted('IS_AUTHENTICATED')]
    public function unfollow(string $username): RedirectResponse
    {
        if ($username == $this->getUser()->getUserIdentifier()) {
            return throw new BadRequestHttpException("You can't unfollow to yourself");
        }

        $follower = $this->followerRepository->findOneBy([
            'follower' => $this->userRepository->findOneBy(['username' => $username])->getId(),
            'followedUser' => $this->getUser()
        ]);

        if ($follower) {
            $this->followerRepository->remove($follower, true);
        }

        return $this->redirectToRoute('app.user.index', [
            'username' => $username
        ]);
    }
}
