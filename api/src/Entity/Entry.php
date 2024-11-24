<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Metadata\ApiFilter;
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
        new GetCollection(
            normalizationContext: ['groups' => [Entry::SERIALIZATION_GROUP_READ_COLLECTION]],
        ),
        new Post(),
    ],
    normalizationContext: ['groups' => [Entry::SERIALIZATION_GROUP_READ_ITEM]],
    denormalizationContext: ['groups' => [Entry::SERIALIZATION_GROUP_WRITE]],
)]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
class Entry
{
    public const SERIALIZATION_GROUP_SNS = 'entry:sns';
    public const SERIALIZATION_GROUP_WRITE = 'entry:write';
    public const SERIALIZATION_GROUP_READ_ITEM = 'entry:read:item';
    public const SERIALIZATION_GROUP_READ_COLLECTION = 'entry:read:collection';

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    #[Serializer\Groups([self::SERIALIZATION_GROUP_READ_ITEM, self::SERIALIZATION_GROUP_READ_COLLECTION])]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    #[Gedmo\Timestampable]
    #[Serializer\Groups([self::SERIALIZATION_GROUP_READ_ITEM])]
    public \DateTimeImmutable $updatedAt;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Serializer\Groups([self::SERIALIZATION_GROUP_READ_ITEM, self::SERIALIZATION_GROUP_READ_COLLECTION])]
    private Uuid $id;

    #[ORM\Column(type: Types::TEXT)]
    #[Serializer\Groups([
        self::SERIALIZATION_GROUP_SNS,
        self::SERIALIZATION_GROUP_WRITE,
        self::SERIALIZATION_GROUP_READ_ITEM,
        self::SERIALIZATION_GROUP_READ_COLLECTION,
    ])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 5000)]
    private string $content;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Gedmo\Blameable(on: 'create')]
    private User $user;

    /**
     * @var Collection<int, EntryMetadata>
     */
    #[ORM\OneToMany(targetEntity: EntryMetadata::class, mappedBy: 'entry', orphanRemoval: true, cascade: ['persist', 'remove'])]
    #[Serializer\Groups([self::SERIALIZATION_GROUP_READ_ITEM])]
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
