<?php

declare(strict_types=1);

namespace App\Entity\Metrics;

use ApiPlatform\Metadata\ApiProperty;
use App\Enum\Metrics\GroupingCriteria;
use App\Metadata\Metrics\MetricsApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

trait MetricsIdentifierTrait
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    public string $doctrineIdentifier;

    #[ORM\Column(type: 'string')]
    #[ApiProperty(required: true, description: 'The unique identifier of the metrics. It could the ID of the entry if grouped by entry, or the formatted date if grouped by date.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    private string $id;

    #[ORM\Column(type: 'string', enumType: GroupingCriteria::class)]
    #[ApiProperty(required: true, description: 'The grouping criteria used to group the metrics.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    private GroupingCriteria $grouping;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getGrouping(): GroupingCriteria
    {
        return $this->grouping;
    }

    public function setGrouping(GroupingCriteria $grouping): static
    {
        $this->grouping = $grouping;

        return $this;
    }
}
