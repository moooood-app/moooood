<?php

namespace App\Repository\Awards;

use App\Entity\Awards\Award;
use App\Entity\Awards\AwardProgress;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AwardProgress>
 */
class AwardProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AwardProgress::class);
    }

    public function deleteAwardProgressForAward(User $user, Award $award): void
    {
        $this->createQueryBuilder('ap')
            ->delete()
            ->where('ap.user = :user')
            ->andWhere('ap.award = :award')
            ->setParameter('user', $user)
            ->setParameter('award', $award)
            ->getQuery()
            ->execute()
        ;
    }

    public function createOrUpdateAwardProgress(User $user, Award $award, int $progress): void
    {
        $entityManager = $this->getEntityManager();
        $awardProgress = $this->findOneBy(['user' => $user, 'award' => $award]);

        if (null === $awardProgress) {
            $awardProgress = (new AwardProgress())
                ->setUser($user)
                ->setAward($award)
            ;
        }

        $awardProgress->setProgress($progress);

        $entityManager->persist($awardProgress);
        $entityManager->flush();
    }
}
