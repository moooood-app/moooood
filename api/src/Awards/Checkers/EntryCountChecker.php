<?php

namespace App\Awards\Checkers;

use App\Awards\AwardAwareTrait;
use App\Awards\AwardStatus;
use App\Awards\ChainableCheckerTrait;
use App\Awards\Contracts\ChainableAwardCheckerInterface;
use App\Entity\User;
use App\Enum\AwardType;
use App\Repository\EntryRepository;

final class EntryCountChecker implements ChainableAwardCheckerInterface
{
    use AwardAwareTrait;
    use ChainableCheckerTrait;

    public const ENTRY_COUNT_CRITERIA = 'entry_count';

    public function __construct(private readonly EntryRepository $entryRepository)
    {
    }

    public static function getSupportedType(): AwardType
    {
        return AwardType::ENTRIES;
    }

    public function check(User $user): AwardStatus
    {
        /** @var array{entry_count?: int} */
        $criteria = $this->award->getCriteria();

        $entryCount = $criteria[self::ENTRY_COUNT_CRITERIA] ?? throw new \InvalidArgumentException('Missing criteria entry_count');

        $userEntryCount = $this->entryRepository->countEntriesForUser($user);

        if (0 === $userEntryCount) {
            return new AwardStatus($this->award, false, 0);
        }

        return new AwardStatus(
            $this->award,
            $userEntryCount >= $entryCount,
            (int) ($userEntryCount / $entryCount * 100),
        );
    }
}
