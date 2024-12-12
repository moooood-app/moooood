<?php

namespace App\Message\Awards;

use App\Entity\Part;
use App\Entity\User;
use Symfony\Component\Serializer\Attribute as Serializer;

final readonly class NewPartEventMessage implements AwardEventMessageInterface
{
    public function __construct(
        private Part $part,
    ) {
    }

    #[Serializer\Groups([self::SERIALIZATION_GROUP_SNS])]
    public function getPart(): Part
    {
        return $this->part;
    }

    #[Serializer\Groups([self::SERIALIZATION_GROUP_SNS])]
    public function getUser(): User
    {
        return $this->part->getUser();
    }
}
