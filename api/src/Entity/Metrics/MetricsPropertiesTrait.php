<?php

declare(strict_types=1);

namespace App\Entity\Metrics;

use ApiPlatform\Metadata\ApiProperty;
use App\Metadata\Metrics\MetricsApiResource;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

trait MetricsPropertiesTrait
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING)]
    private string $id;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    #[ApiProperty(description: 'The first day of the period covered by the metrics.')]
    private \DateTimeImmutable $date;

    public function getId(): string
    {
        return $this->id;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }
}
