<?php

namespace App\Repository\Awards;

use App\Entity\Awards\Award;
use App\Entity\Awards\GrantedAward;
use App\Entity\User;
use App\Enum\AwardType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Award>
 */
class AwardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Award::class);
    }

    /**
     * @return list<Award>
     */
    public function findNonGrantedAwards(User $user, ?AwardType $type = null): iterable
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin(
                GrantedAward::class,
                'ga',
                Join::WITH,
                'ga.award = a.id AND ga.user = :user',
            )
            ->where('ga.id IS NULL')
            ->setParameter('user', $user)
        ;

        if (null !== $type) {
            $qb->andWhere('a.type = :type')->setParameter('type', $type);
        }

        return $qb->getQuery()->getResult();
    }
}
