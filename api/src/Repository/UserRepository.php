<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Awards\Award;
use App\Entity\Awards\GrantedAward;
use App\Entity\Entry;
use App\Entity\User;
use App\Enum\AwardType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @implements PasswordUpgraderInterface<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param User $user
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @return array<User>
     */
    public function findUsersWithEntriesInConsecutivePeriodsNotGrantedAwardType(
        \DateInterval $interval,
        AwardType $awardType,
        int $minEntriesPerPeriod,
    ): iterable {
        $interval = \DateInterval::createFromDateString('1 week');

        $now = new \DateTime();
        $firstPeriodEndDate = (clone $now)->setTime(0, 0, 0, 0);
        $firstPeriodStartDate = (clone $firstPeriodEndDate)->sub($interval);

        $secondPeriodEndDate = $firstPeriodStartDate;
        $secondPeriodStartDate = (clone $secondPeriodEndDate)->sub($interval);

        $qb = $this->getEntityManager()->createQueryBuilder();

        $query = $qb
            ->select('u')
            ->from(User::class, 'u')
            ->innerJoin(Entry::class, 'e', Join::WITH, 'e.user = u.id')
            ->leftJoin(GrantedAward::class, 'ga', Join::WITH, 'ga.user = u.id')
            ->leftJoin(Award::class, 'a', Join::WITH, 'a.id = ga.award AND a.type = :awardType')
            ->where($qb->expr()->orX(
                $qb->expr()->isNull('ga.id'),
                $qb->expr()->isNull('a.id'),
            ))
            ->andWhere('e.createdAt BETWEEN :secondPeriodStartDate AND :firstPeriodEndDate')
            ->groupBy('u.id')
            ->having(
                $qb->expr()->andX(
                    'SUM(CASE WHEN e.createdAt BETWEEN :firstPeriodStartDate AND :firstPeriodEndDate THEN 1 ELSE 0 END) >= :minEntries',
                    'SUM(CASE WHEN e.createdAt BETWEEN :secondPeriodStartDate AND :secondPeriodEndDate THEN 1 ELSE 0 END) >= :minEntries'
                )
            )
            ->setParameters(new ArrayCollection([
                new Parameter('firstPeriodStartDate', $firstPeriodStartDate),
                new Parameter('firstPeriodEndDate', $firstPeriodEndDate),
                new Parameter('secondPeriodStartDate', $secondPeriodStartDate),
                new Parameter('secondPeriodEndDate', $secondPeriodEndDate),
                new Parameter('minEntries', $minEntriesPerPeriod),
                new Parameter('awardType', $awardType),
            ]))
            ->getQuery()
        ;

        return $query->getResult();
    }
}
