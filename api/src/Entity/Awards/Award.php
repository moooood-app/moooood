<?php

namespace App\Entity\Awards;

use App\Enum\AwardType;
use App\Repository\Awards\AwardRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AwardRepository::class)]
class Award
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 1024)]
    private string $description;

    #[ORM\Column(length: 512)]
    private string $image;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $criteria = [];

    #[ORM\Column(enumType: AwardType::class)]
    private AwardType $type;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $priority;

    public function getId(): Uuid
    {
        return $this->id;
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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCriteria(): array
    {
        return $this->criteria;
    }

    /**
     * @param array<string, mixed> $criteria
     */
    public function setCriteria(array $criteria): static
    {
        $this->criteria = $criteria;

        return $this;
    }

    public function getType(): AwardType
    {
        return $this->type;
    }

    public function setType(AwardType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }
}
