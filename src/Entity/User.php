<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\{PasswordAuthenticatedUserInterface, UserInterface};

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string|null The unique username
     */
    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "Please enter your username")]
    private ?string $username = null;

    /**
     * @var string|null The hashed password
     */
    #[ORM\Column]
    #[Assert\Regex("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z0-9]{8,}$/", message: "This password is not valid")]
    #[Assert\NotBlank(message: "Please enter your password")]
    private ?string $password = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $avatar;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private bool $isBanned = false;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Post::class, orphanRemoval: true)]
    private Collection $posts;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Comment::class, orphanRemoval: true)]
    private Collection $comments;

    #[ORM\Column]
    private ?DateTimeImmutable $created_at;

    #[ORM\OneToMany(mappedBy: 'follower', targetEntity: Follower::class, orphanRemoval: true)]
    private Collection $followers;

    #[ORM\OneToMany(mappedBy: 'followedUser', targetEntity: Follower::class, orphanRemoval: true)]
    private Collection $followedUsers;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Message::class)]
    private Collection $messages;

    #[ORM\ManyToMany(targetEntity: Conversation::class, mappedBy: 'users')]
    private Collection $conversations;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();

        $this->created_at = new DateTimeImmutable();

        $this->followers = new ArrayCollection();
        $this->followedUsers = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->conversations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function resetRoles(): static
    {
        $this->roles = [];

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setAuthor($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->created_at;
    }

    /**
     * @return Collection<int, Follower>
     */
    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function addFollower(Follower $follower): static
    {
        if (!$this->followers->contains($follower)) {
            $this->followers->add($follower);
            $follower->setFollower($this);
        }

        return $this;
    }

    public function removeFollower(Follower $follower): static
    {
        if ($this->followers->removeElement($follower)) {
            // set the owning side to null (unless already changed)
            if ($follower->getFollower() === $this) {
                $follower->setFollower(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Follower>
     */
    public function getFollowedUsers(): Collection
    {
        return $this->followedUsers;
    }

    public function addFollowedUser(Follower $followedUser): static
    {
        if (!$this->followedUsers->contains($followedUser)) {
            $this->followedUsers->add($followedUser);
            $followedUser->setFollowedUser($this);
        }

        return $this;
    }

    public function removeFollowedUser(Follower $followedUser): static
    {
        if ($this->followedUsers->removeElement($followedUser)) {
            // set the owning side to null (unless already changed)
            if ($followedUser->getFollowedUser() === $this) {
                $followedUser->setFollowedUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setUser($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getUser() === $this) {
                $message->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Conversation>
     */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation): static
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations->add($conversation);
            $conversation->addUser($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): static
    {
        if ($this->conversations->removeElement($conversation)) {
            $conversation->removeUser($this);
        }

        return $this;
    }

    public function isBanned(): bool
    {
        return $this->isBanned;
    }

    public function setIsBanned(bool $isBanned): void
    {
        $this->isBanned = $isBanned;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;
    }
}
