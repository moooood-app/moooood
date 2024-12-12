<?php

namespace App\Awards\Checkers;

use App\Awards\AwardStatus;
use App\Awards\AwardStatusCollection;
use App\Entity\User;
use App\Enum\AwardType;
use App\Repository\EntryRepository;

final class EntryCountChecker extends AbstractChainableAwardChecker
{
    public const ENTRY_COUNT_CRITERIA = 'entry_count';

    public function __construct(private readonly EntryRepository $entryRepository)
    {
    }

    public static function getSupportedType(): AwardType
    {
        return AwardType::ENTRIES;
    }

    public function check(User $user, AwardStatusCollection $awardStatusCollection): void
    {
        /** @var array{entry_count?: int} */
        $criteria = $this->award->getCriteria();

        $entryCount = $criteria[self::ENTRY_COUNT_CRITERIA] ?? throw new \InvalidArgumentException('Missing criteria entry_count');

        $userEntryCount = $this->entryRepository->countEntriesForUser($user);

        if (0 === $userEntryCount) {
            $status = new AwardStatus($this->award, false, 0);
        }

        $status = new AwardStatus(
            $this->award,
            $userEntryCount >= $entryCount,
            (int) ($userEntryCount / $entryCount * 100),
        );

        $awardStatusCollection->add($status);

        if ($status->isGranted() && null !== $this->next) {
            $this->next->check($user, $awardStatusCollection);
        }
    }
}
