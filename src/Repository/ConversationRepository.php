<?php

namespace App\Repository;

use App\Entity\{User, Conversation};
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Conversation>
 *
 * @method Conversation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conversation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conversation[]    findAll()
 * @method Conversation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    public function save(Conversation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Conversation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUsers(User|UserInterface $firstUser, User $secondUser): array
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->leftJoin('c.users', 'u')
            ->andWhere(':firstUser MEMBER OF c.users')
            ->andWhere(':secondUser MEMBER OF c.users')
            ->andWhere(
                'CASE WHEN SIZE(c.users) = 2 THEN 2 ELSE SIZE(c.users) END = 2'
            )
            ->setParameter('firstUser', $firstUser)
            ->setParameter('secondUser', $secondUser)
            ->getQuery()
            ->getResult();
    }
}
