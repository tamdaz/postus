<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }

    public function findSuggestUsers(): array
    {
        $subQuery = $this->createQueryBuilder('sub')
            ->select('count(followers.id)')
            ->leftJoin('sub.followers', 'followers')
            ->where('sub = u')
            ->getDQL();

        return $this->createQueryBuilder('u')
            ->select('u.username, u.created_at, (' . $subQuery . ') AS followersCount')
            ->orderBy('followersCount', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findFollowedUser(int $userId, string $username = ''): array
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->join('u.followers', 'f')
            ->andWhere('f.followedUser = :userId')
            ->setParameter('userId', $userId);

        if ($username !== '') {
            $queryBuilder
                ->andWhere('u.username LIKE :username')
                ->setParameter('username', '%' . $username . '%');
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
