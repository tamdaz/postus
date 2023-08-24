<?php

namespace App\Controller;

use App\Service\ConversationService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\{UserRepository, ConversationRepository};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Response, RedirectResponse};
use Symfony\Component\HttpKernel\Exception\{AccessDeniedHttpException, NotFoundHttpException};

#[IsGranted('IS_AUTHENTICATED')]
class ConversationController extends AbstractController
{
    public function __construct(
        protected ConversationRepository $conversationRepository,
        protected UserRepository $userRepository
    ) {}

    #[Route('/conversation', name: 'app.conversation.index')]
    public function index(): Response
    {
        return $this->render('conversation/index.html.twig', [
            'conversations' => $this->userRepository->find($this->getUser())->getConversations()
        ]);
    }

    #[Route('/conversation/new', name: 'app.conversation.new')]
    public function new(): Response
    {
        return $this->render('conversation/new.html.twig');
    }

    #[Route('/conversation/find/@{username}', name: 'app.conversation.find')]
    public function find(string $username, ConversationService $conversationService): RedirectResponse
    {
        $conversation = $this->conversationRepository->findByUsers(
            $this->getUser(),
            $this->userRepository->findOneBy(['username' => $username])
        );

        if (empty($conversation)) {
            [$owner, $interlocutor] = [
                $this->userRepository->find($this->getUser())->getId(),
                $this->userRepository->findOneBy(['username' => $username])
            ];

            return $conversationService->create([$owner, $interlocutor], $owner);
        }

        return $this->redirectToRoute('app.conversation.show', [
            'uuid' => $conversation[0]->getId()->jsonSerialize()
        ]);
    }

    #[Route('/conversation/{uuid}', name: 'app.conversation.show')]
    public function show(string $uuid): Response
    {
        $conversation = $this->conversationRepository->find($uuid);

        if (!$conversation) {
            return throw new NotFoundHttpException("Conversation not found");
        }

        foreach ($conversation->getUsers()->getValues() as $user) {
            if ($user->getUserIdentifier() === $this->getUser()->getUserIdentifier()) {
                if (count($conversation->getUsers()->toArray()) == 2) {
                    // Show only the destination user, not the connected user
                    $destinationUser = array_filter($conversation->getUsers()->toArray(), function ($v) {
                        return $v->getUserIdentifier() !== $this->getUser()->getUserIdentifier();
                    });

                    return $this->render('conversation/show.html.twig', [
                        'destination_user' => reset($destinationUser),
                        'messages' => $conversation->getMessages(),
                        'uuid' => $uuid
                    ]);
                }

                if (count($conversation->getUsers()->toArray()) > 2) {
                    return $this->render('conversation/show.html.twig', [
                        'users' => $conversation->getUsers()->getValues(),
                        'owner' => $conversation->getOwner(),
                        'messages' => $conversation->getMessages(),
                        'uuid' => $uuid
                    ]);
                }
            }
        }

        return throw new AccessDeniedHttpException("You can't access to this conversation");
    }

    #[Route('/conversation/{uuid}/delete', name: 'app.conversation.delete', methods: "POST")]
    public function delete(string $uuid): RedirectResponse
    {
        $this->conversationRepository->remove($this->conversationRepository->find($uuid), true);

        return $this->redirectToRoute('app.conversation.index');
    }
}
