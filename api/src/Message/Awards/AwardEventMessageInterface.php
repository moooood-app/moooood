<?php

namespace App\Message\Awards;

use App\Entity\User;

interface AwardEventMessageInterface
{
    public const SERIALIZATION_GROUP_SNS = 'sns';

    public function getUser(): User;
}
