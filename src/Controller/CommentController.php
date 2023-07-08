<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\{CommentRepository, PostRepository};
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{Request, RedirectResponse};

#[IsGranted('IS_AUTHENTICATED')]
#[Route('/post/{pid}/comment')]
class CommentController extends AbstractController
{
    public function __construct(
        protected CommentRepository $commentRepository,
        protected PostRepository $postRepository
    ) {}

    #[Route('/new', name: 'app.comment.new', methods: 'POST')]
    public function new(int $pid, Request $request, Comment $comment): RedirectResponse
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            if ($form->isSubmitted() && $form->isValid()) {
                $comment
                    ->setAuthor($this->getUser())
                    ->setPost($this->postRepository->find($pid));

                $this->commentRepository->save($comment, true);

                $this->addFlash('success', 'Comment successfully published !');

                return $this->redirectToRoute('app.post.show', [
                    'id' => $pid
                ]);
            }
        }

        $this->addFlash('error', 'An error has been occurred !');

        return $this->redirectToRoute('app.post.show', [
            'id' => $pid
        ]);
    }

    #[Route('/{cid}/delete', name: 'app.comment.remove')]
    public function remove(int $pid, int $cid): RedirectResponse
    {
        $this->commentRepository->remove(
            $this->commentRepository->find($cid),
            true
        );

        $this->addFlash('success', 'Comment successfully deleted !');

        return $this->redirectToRoute('app.post.show', [
            'id' => $pid
        ]);
    }
}
