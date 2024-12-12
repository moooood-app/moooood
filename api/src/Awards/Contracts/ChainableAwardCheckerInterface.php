<?php

namespace App\Awards\Contracts;

use App\Enum\AwardType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(ChainableAwardCheckerInterface::CHECKER_TAG)]
interface ChainableAwardCheckerInterface extends AwardCheckerInterface, AwardAwareInterface
{
    public const CHECKER_TAG = 'award.checker';

    public static function getSupportedType(): AwardType;

    public function setNext(self $checker): static;
}
