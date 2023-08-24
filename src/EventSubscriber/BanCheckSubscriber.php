<?php

namespace App\EventSubscriber;

use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BanCheckSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected Security $security,
        protected UserRepository $userRepository,
    ) { }

    public function onKernelRequest(RequestEvent $event): void
    {
        $path = $event->getRequest()->getPathInfo();

        if (!empty($this->security->getUser())) {
            $userSecurity = $this->security->getUser();
            $user = $this->userRepository->findOneBy([
                'username' => $userSecurity->getUserIdentifier()
            ]);

            if ($user->isBanned() === true && $event->getRequest()->getPathInfo() !== "/ban") {
                $event->setResponse(new RedirectResponse("/ban"));
            }
        }

        unset($path);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest'
        ];
    }
}
