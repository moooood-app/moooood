<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
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
    ],
    normalizationContext: ['groups' => [self::SERIALIZATION_GROUP_READ_ITEM]],
    denormalizationContext: ['groups' => [self::SERIALIZATION_GROUP_WRITE]],
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const SERIALIZATION_GROUP_WRITE = 'users:write';
    public const SERIALIZATION_GROUP_READ_ITEM = 'users:read:item';

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups([self::SERIALIZATION_GROUP_READ_ITEM])]
    #[ApiProperty(identifier: true, description: 'The UUID of the user')]
    private Uuid $id;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    #[Groups([self::SERIALIZATION_GROUP_READ_ITEM, self::SERIALIZATION_GROUP_WRITE])]
    #[ApiProperty(description: 'The first name of the user')]
    private string $firstName;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    #[Groups([self::SERIALIZATION_GROUP_READ_ITEM, self::SERIALIZATION_GROUP_WRITE])]
    #[ApiProperty(description: 'The last name of the user')]
    private string $lastName;

    #[ORM\Column(length: 320)]
    #[Assert\NotBlank]
    #[Assert\Email(mode: Assert\Email::VALIDATION_MODE_STRICT)]
    #[Groups([self::SERIALIZATION_GROUP_READ_ITEM, self::SERIALIZATION_GROUP_WRITE])]
    #[ApiProperty(description: 'The email/username of the user')]
    private string $email;

    #[ORM\Column(length: 255)]
    private string $password;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $google = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $apple = null;

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

    public function getEmail(): string
    {
        return $this->email;
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
}
