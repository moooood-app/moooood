<?php

namespace App\Awards\Contracts;

use App\Awards\AwardStatus;
use App\Entity\User;
use App\Enum\AwardType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(AwardCheckerInterface::CHECKER_TAG)]
interface AwardCheckerInterface extends AwardAwareInterface
{
    public const CHECKER_TAG = 'award.checker';

    public static function getSupportedType(): AwardType;

    public function check(User $user): AwardStatus;
}
