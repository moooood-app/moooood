<?php

declare(strict_types=1);

namespace App\Entity\Metrics;

use ApiPlatform\Metadata\ApiProperty;
use App\Enum\Processor;
use App\Metadata\Metrics\MetricsApiResource;
use App\Repository\Metrics\EmotionsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

#[ORM\MappedSuperclass(repositoryClass: EmotionsRepository::class)]
#[MetricsApiResource(metricsType: Processor::EMOTIONS)]
class Emotions implements MetricsIdentifierInterface
{
    use MetricsPropertiesTrait;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The joy score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $joy;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The fear score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $fear;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The love score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $love;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The anger score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $anger;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The grief score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $grief;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The pride score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $pride;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The caring score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $caring;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The desire score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $desire;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The relief score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $relief;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The disgust score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $disgust;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The neutral score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $neutral;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The remorse score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $remorse;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The sadness score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $sadness;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The approval score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $approval;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The optimism score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $optimism;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The surprise score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $surprise;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The amusement score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $amusement;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The annoyance score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $annoyance;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The confusion score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $confusion;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The curiosity score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $curiosity;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The gratitude score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $gratitude;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The admiration score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $admiration;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The excitement score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $excitement;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The disapproval score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $disapproval;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The nervousness score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $nervousness;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The realization score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $realization;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The embarrassment score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $embarrassment;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The disappointment score, from 0 to 1.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public float $disappointment;
}
