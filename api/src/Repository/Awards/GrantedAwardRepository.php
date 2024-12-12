<?php

namespace App\Repository\Awards;

use App\Entity\Awards\Award;
use App\Entity\Awards\GrantedAward;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GrantedAward>
 */
class GrantedAwardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GrantedAward::class);
    }

    public function grantAward(Award $award, User $user): GrantedAward
    {
        $entityManager = $this->getEntityManager();

        $grantedAward = (new GrantedAward())
            ->setAward($award)
            ->setUser($user)
        ;

        $entityManager->persist($grantedAward);
        $entityManager->flush();

        return $grantedAward;
    }
}
