<?php

namespace App\Controller;

use App\Entity\Post;
use Knp\Component\Pager\PaginatorInterface;
use App\Form\{PostType, CommentType};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\{Request, Response, RedirectResponse};
use App\Repository\{PostRepository, UserRepository, CommentRepository, FollowerRepository};
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('IS_AUTHENTICATED')]
class PostController extends AbstractController
{
    public function __construct(
        protected FollowerRepository $followerRepository,
        protected CommentRepository $commentRepository,
        protected TranslatorInterface $translator,
        protected UserRepository $userRepository,
        protected PostRepository $postRepository,
        protected PostType $postType,
    ) {}

    #[Route('/', name: 'app.post.index', methods: ['GET', 'POST'])]
    public function index(PaginatorInterface $paginator, Request $request): Response|RedirectResponse
    {
        $paginationPosts = $paginator->paginate(
            $this->postRepository->findAllRecentPosts(),
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('post/index.html.twig', [
            'posts' => $paginationPosts,
            'suggestion_users' => $this->userRepository->findSuggestUsers(),
            'form' => $this->createForm(PostType::class)->createView()
        ]);
    }

    #[Route('/post/new', name: 'app.post.new', methods: 'POST')]
    public function new(Request $request, Post $post): RedirectResponse
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthor($this->getUser());
            $this->postRepository->save($post, true);
            $this->addFlash('success', $this->translator->trans('Post successfully published !', domain: 'posts'));

            return $this->redirectToRoute('app.post.index');
        }

        $this->addFlash('error', 'An error has been occurred');

        return $this->redirectToRoute('app.post.index');
    }

    #[Route('/post/{id}', name: 'app.post.show', methods: ['GET', 'POST'])]
    public function show(int $id): Response|RedirectResponse
    {
        $post = $this->postRepository->find($id);

        if (!$post) {
            return throw new NotFoundHttpException("Post $id not found");
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'form' => $this->createForm(CommentType::class)->createView()
        ]);
    }

    #[Route('/post/{id}/edit', name: 'app.post.edit', methods: ['GET', 'POST'])]
    #[IsGranted('POST_EDIT', 'post', "You can't edit the post from another user")]
    public function edit(int $id, Post $post, Request $request): RedirectResponse|Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            if ($form->isSubmitted() && $form->isValid()) {
                $post = $form->getData();

                $this->postRepository->save($post, true);

                $this->addFlash('success', $this->translator->trans('Post successfully updated !', domain: 'posts'));

                return $this->redirectToRoute('app.post.show', [
                    'id' => $id
                ]);
            }
        }

        return $this->render('post/_form.html.twig', [
            'form' => $form->createView(),
            'is_editing' => true
        ]);
    }

    #[Route('/post/{id}/delete', name: 'app.post.delete')]
    #[IsGranted('POST_DELETE', 'post', "You can't delete the post from another user")]
    public function delete(Post $post): RedirectResponse
    {
        $this->postRepository->remove($post, true);
        $this->addFlash('success', $this->translator->trans('Post successfully deleted !', domain: 'posts'));

        return $this->redirectToRoute('app.post.index');
    }
}
