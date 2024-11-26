<?php

namespace App\Entity\Metrics;

use ApiPlatform\Metadata\ApiProperty;
use App\Entity\Part;
use App\Metadata\Metrics\MetricsApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

trait PartTrait
{
    #[ORM\ManyToOne(targetEntity: Part::class)]
    #[ApiProperty(description: 'The part associated with the metrics.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public ?Part $part = null;
}
