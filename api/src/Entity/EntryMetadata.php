<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\Processor;
use App\Repository\EntryMetadataRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation as Serializer;

#[ORM\Table(uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'unique_entry_processor', columns: ['entry_id', 'processor']),
])]
#[ORM\Entity(repositoryClass: EntryMetadataRepository::class)]
#[ORM\Index(name: 'idx_processor', columns: ['processor'])]
#[ORM\Index(name: 'idx_created_at', columns: ['created_at'])]
class EntryMetadata
{
    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    #[Gedmo\Timestampable(on: 'create')]
    #[Serializer\Groups([Entry::SERIALIZATION_GROUP_READ_ITEM])]
    public ?\DateTimeImmutable $createdAt = null;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::JSON, options: ['jsonb' => true])]
    #[Serializer\Groups([Entry::SERIALIZATION_GROUP_READ_ITEM])]
    private array $metadata = [];

    #[ORM\Column(type: Types::STRING, enumType: Processor::class)]
    #[Serializer\Groups([Entry::SERIALIZATION_GROUP_READ_ITEM])]
    private Processor $processor;

    #[ORM\ManyToOne(inversedBy: 'metadata')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entry $entry = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getProcessor(): Processor
    {
        return $this->processor;
    }

    public function setProcessor(Processor $processor): static
    {
        $this->processor = $processor;

        return $this;
    }

    public function getEntry(): ?Entry
    {
        return $this->entry;
    }

    public function setEntry(?Entry $entry): static
    {
        $this->entry = $entry;

        return $this;
    }
}
