<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\PartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Annotation as Serializer;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\CssColor;

#[ORM\Entity(repositoryClass: PartRepository::class)]
#[ORM\Table(name: 'parts')]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_READ_COLLECTION]]),
        new Patch(),
        new Post(),
    ],
    normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_READ_ITEM]],
    denormalizationContext: ['groups' => [self::SERIALIZATION_GROUP_WRITE]],
)]
class Part
{
    public const SERIALIZATION_GROUP_WRITE = 'part:write';
    public const SERIALIZATION_GROUP_READ_ITEM = 'part:read:item';
    public const SERIALIZATION_GROUP_READ_COLLECTION = 'part:read:collection';
    public const SERIALIZATION_GROUP_MINIMAL = 'part:read:minimal';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Gedmo\Blameable(on: 'create')]
    private User $user;

    #[ORM\Column(length: 100)]
    #[Assert\Length(min: 1, max: 100)]
    #[Serializer\Groups([
        self::SERIALIZATION_GROUP_READ_ITEM,
        self::SERIALIZATION_GROUP_READ_COLLECTION,
        self::SERIALIZATION_GROUP_MINIMAL,
        self::SERIALIZATION_GROUP_WRITE,
    ])]
    private string $name;

    /**
     * @var array<string>
     */
    #[ORM\Column(type: Types::JSON, options: ['jsonb' => true, 'default' => '[]'])]
    #[Assert\All([new CssColor(CssColor::HEX_LONG)])]
    #[Assert\Count(
        exactly: 5,
        exactMessage: 'You must specify exactly {{ limit }} colors',
    )]
    #[Assert\Unique(message: 'You cannot use the same color twice')]
    #[Serializer\Groups([self::SERIALIZATION_GROUP_READ_ITEM, self::SERIALIZATION_GROUP_READ_COLLECTION, self::SERIALIZATION_GROUP_WRITE])]
    private array $colors = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    #[Serializer\Groups([self::SERIALIZATION_GROUP_READ_ITEM])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Gedmo\Timestampable]
    #[Serializer\Groups([self::SERIALIZATION_GROUP_READ_ITEM])]
    private \DateTimeImmutable $updatedAt;

    /**
     * @var Collection<int, Entry>
     */
    #[ORM\OneToMany(targetEntity: Entry::class, mappedBy: 'part', fetch: 'LAZY', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $entries;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getColors(): array
    {
        return $this->colors;
    }

    /**
     * @param array<string> $colors
     */
    public function setColors(array $colors): static
    {
        $this->colors = $colors;

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
     * @return Collection<int, Entry>
     */
    public function getEntries(): Collection
    {
        return $this->entries;
    }

    public function addEntry(Entry $entry): static
    {
        if (!$this->entries->contains($entry)) {
            $this->entries->add($entry);
        }

        return $this;
    }

    public function removeEntry(Entry $entry): static
    {
        $this->entries->removeElement($entry);

        return $this;
    }
}
