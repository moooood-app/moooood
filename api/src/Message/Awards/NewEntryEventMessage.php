<?php

namespace App\Message\Awards;

use App\Entity\Entry;
use App\Entity\User;
use Symfony\Component\Serializer\Attribute as Serializer;

final readonly class NewEntryEventMessage implements AwardEventMessageInterface
{
    #[Serializer\Groups([self::SERIALIZATION_GROUP_SNS])]
    private Entry $entry;

    #[Serializer\Groups([self::SERIALIZATION_GROUP_SNS])]
    private User $user;

    public function __construct(Entry $entry)
    {
        $this->entry = $entry;
        $this->user = $entry->getUser();
    }

    public function getEntry(): Entry
    {
        return $this->entry;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
