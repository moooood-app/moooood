<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\EntryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation as Serializer;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'entries')]
#[ORM\Entity(repositoryClass: EntryRepository::class)]
#[ORM\Index(name: 'idx_entry_created_at', columns: ['created_at'])]
#[ORM\Index(name: 'idx_entry_content_fulltext', columns: ['content'], flags: ['fulltext'])]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(normalizationContext: [
            'groups' => [self::SERIALIZATION_GROUP_READ_COLLECTION],
        ]),
        new Post(),
    ],
    normalizationContext: [
        'groups' => [self::SERIALIZATION_GROUP_READ_ITEM],
    ],
    denormalizationContext: ['groups' => [self::SERIALIZATION_GROUP_WRITE]],
)]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
class Entry
{
    public const SERIALIZATION_GROUP_WRITE = 'entry:write';
    public const SERIALIZATION_GROUP_READ_ITEM = 'entry:read:item';
    public const SERIALIZATION_GROUP_READ_COLLECTION = 'entry:read:collection';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid $id;

    #[ORM\Column(type: Types::TEXT)]
    #[Serializer\Groups([
        self::SERIALIZATION_GROUP_WRITE,
        self::SERIALIZATION_GROUP_READ_ITEM,
        self::SERIALIZATION_GROUP_READ_COLLECTION,
    ])]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 10,
        max: 5000,
        minMessage: 'An entry must be at least {{ limit }} characters long',
        maxMessage: 'An entry cannot be longer than {{ limit }} characters',
    )]
    #[ApiProperty(description: 'The content of the entry')]
    private string $content;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'entries')]
    #[ORM\JoinColumn(nullable: false)]
    #[Gedmo\Blameable(on: 'create')]
    private User $user;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    #[Serializer\Groups([self::SERIALIZATION_GROUP_READ_ITEM, self::SERIALIZATION_GROUP_READ_COLLECTION])]
    #[ApiProperty(description: 'The date and time the entry was created')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Gedmo\Timestampable]
    #[Serializer\Groups([self::SERIALIZATION_GROUP_READ_ITEM, self::SERIALIZATION_GROUP_READ_COLLECTION])]
    #[ApiProperty(description: 'The date and time the entry was last updated. Updated when new metadata is added.')]
    private \DateTimeImmutable $updatedAt;

    /**
     * @var Collection<int, EntryMetadata>
     */
    #[ORM\OneToMany(
        targetEntity: EntryMetadata::class,
        mappedBy: 'entry',
        orphanRemoval: true,
        cascade: ['persist', 'remove'],
    )]
    #[Serializer\Groups([self::SERIALIZATION_GROUP_READ_ITEM])]
    #[ApiProperty(description: 'The metadata of the entry')]
    private Collection $metadata;

    public function __construct()
    {
        $this->metadata = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, EntryMetadata>
     */
    public function getMetadata(): Collection
    {
        return $this->metadata;
    }

    public function addMetadata(EntryMetadata $metadata): static
    {
        if (!$this->metadata->contains($metadata)) {
            $this->metadata->add($metadata);
            $metadata->setEntry($this);
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function removeMetadata(EntryMetadata $metadata): static
    {
        $this->metadata->removeElement($metadata);

        return $this;
    }
}
