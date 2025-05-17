<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use App\Entity\Awards\GrantedAward;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'users')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    operations: [
        new Get(
            security: 'object.getId() == user.getId()',
            securityMessage: 'You are not allowed to see this user',
        ),
        new Patch(
            security: 'object.getId() == user.getId()',
            securityMessage: 'You are not allowed to edit this user',
        ),
        new Delete(
            security: 'object.getId() == user.getId()',
            securityMessage: 'You are not allowed to delete this user',
        ),
    ],
    normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_READ_ITEM]],
    denormalizationContext: ['groups' => [self::SERIALIZATION_GROUP_WRITE]],
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const SERIALIZATION_GROUP_WRITE = 'users:write';
    public const SERIALIZATION_GROUP_READ_ITEM = 'users:read:item';
    public const SERIALIZATION_GROUP_JWT = 'users:jwt';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid $id;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    #[Serializer\Groups([self::SERIALIZATION_GROUP_READ_ITEM, self::SERIALIZATION_GROUP_JWT, self::SERIALIZATION_GROUP_WRITE])]
    #[ApiProperty(description: 'The first name of the user')]
    private string $firstName;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    #[Serializer\Groups([self::SERIALIZATION_GROUP_READ_ITEM, self::SERIALIZATION_GROUP_JWT, self::SERIALIZATION_GROUP_WRITE])]
    #[ApiProperty(description: 'The last name of the user')]
    private string $lastName;

    #[ORM\Column(length: 320)]
    #[Assert\NotBlank]
    #[Assert\Email(mode: Assert\Email::VALIDATION_MODE_STRICT)]
    #[Serializer\Groups([self::SERIALIZATION_GROUP_READ_ITEM, self::SERIALIZATION_GROUP_JWT, self::SERIALIZATION_GROUP_WRITE])]
    #[ApiProperty(description: 'The email/username of the user')]
    private string $email;

    #[ORM\Column(length: 255)]
    private string $password;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $google = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $apple = null;

    #[ORM\Column(type: 'string', length: 64, nullable: false)]
    #[Assert\NotBlank(message: 'Timezone cannot be blank.')]
    #[Assert\Timezone(message: 'Invalid timezone.')]
    #[Serializer\Groups([self::SERIALIZATION_GROUP_JWT])]
    private string $timezone;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, Part>
     */
    #[ORM\OneToMany(
        targetEntity: Part::class,
        mappedBy: 'user',
        fetch: 'EAGER',
        orphanRemoval: true,
        cascade: ['persist', 'remove'],
    )]
    private Collection $parts;

    /**
     * @var Collection<int, Entry>
     */
    #[ORM\OneToMany(
        targetEntity: Entry::class,
        mappedBy: 'user',
        fetch: 'EXTRA_LAZY',
        orphanRemoval: true,
        cascade: ['persist', 'remove'],
    )]
    private Collection $entries;

    /**
     * @var Collection<int, GrantedAward>
     */
    #[ORM\OneToMany(targetEntity: GrantedAward::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $grantedAwards;

    public function __construct()
    {
        $this->parts = new ArrayCollection();
        $this->entries = new ArrayCollection();
        $this->grantedAwards = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastname(): string
    {
        return $this->lastName;
    }

    public function setLastname(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return non-empty-string
     */
    public function getEmail(): string
    {
        return $this->email; // @phpstan-ignore-line
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getGoogle(): ?string
    {
        return $this->google;
    }

    public function setGoogle(string $google): static
    {
        $this->google = $google;

        return $this;
    }

    public function getApple(): ?string
    {
        return $this->apple;
    }

    public function setApple(string $apple): static
    {
        $this->apple = $apple;

        return $this;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

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

    /**
     * @return Collection<int, Part>
     */
    public function getParts(): Collection
    {
        return $this->parts;
    }

    public function addPart(Part $part): self
    {
        if (!$this->parts->contains($part)) {
            $this->parts->add($part);
        }

        return $this;
    }

    public function removePart(Part $part): self
    {
        $this->parts->removeElement($part);

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

    public function getRoles(): array
    {
        return [];
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    /**
     * @return Collection<int, GrantedAward>
     */
    public function getGrantedAwards(): Collection
    {
        return $this->grantedAwards;
    }

    public function addGrantedAward(GrantedAward $grantedAward): static
    {
        if (!$this->grantedAwards->contains($grantedAward)) {
            $this->grantedAwards->add($grantedAward);
            $grantedAward->setUser($this);
        }

        return $this;
    }

    public function removeGrantedAward(GrantedAward $grantedAward): static
    {
        $this->grantedAwards->removeElement($grantedAward);

        return $this;
    }
}
