<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\{Get, GetCollection, Post};
use App\Repository\EntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EntryRepository::class)]
#[ORM\Index(name: 'idx_created_at', columns: ['created_at'])]
#[ORM\Index(name: 'idx_content_fulltext', columns: ['content'], flags: ['fulltext'])] // PostgreSQL-specific
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(normalizationContext: ['groups' => [Entry::SERIALIZATION_GROUP_READ_COLLECTION]]),
        new Post(),
    ],
    normalizationContext: ['groups' => [Entry::SERIALIZATION_GROUP_READ_ITEM]],
    denormalizationContext: ['groups' => [Entry::SERIALIZATION_GROUP_WRITE]],
)]
class Entry
{
    public const SERIALIZATION_GROUP_SNS = 'entry:sns';
    public const SERIALIZATION_GROUP_WRITE = 'entry:write';
    public const SERIALIZATION_GROUP_READ_ITEM = 'entry:read:item';
    public const SERIALIZATION_GROUP_READ_COLLECTION = 'entry:read:collection';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups([self::SERIALIZATION_GROUP_READ_ITEM])]
    private Uuid $id;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups([self::SERIALIZATION_GROUP_WRITE, self::SERIALIZATION_GROUP_READ_ITEM, self::SERIALIZATION_GROUP_SNS])]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups([self::SERIALIZATION_GROUP_READ_ITEM])]
    public ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    #[Gedmo\Timestampable]
    #[Groups([self::SERIALIZATION_GROUP_READ_ITEM])]
    public ?\DateTimeImmutable $updatedAt = null;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }
}
