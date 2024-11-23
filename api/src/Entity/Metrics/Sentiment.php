<?php

declare(strict_types=1);

namespace App\Entity\Metrics;

use ApiPlatform\Metadata\ApiProperty;
use App\Enum\Processor;
use App\Metadata\Metrics\MetricsApiResource;
use App\Repository\Metrics\SentimentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass(repositoryClass: SentimentRepository::class)]
#[MetricsApiResource(metricsType: Processor::SENTIMENT)]
class Sentiment implements MetricsIdentifierInterface
{
    use MetricsIdentifierTrait;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The positive sentiment score, from 0 to 1.')]
    public float $positive;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The neutral sentiment score, from 0 to 1.')]
    public float $neutral;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The negative sentiment score, from 0 to 1.')]
    public float $negative;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    #[ApiProperty(description: 'The compound sentiment score, from -1 to 1.')]
    public float $compound;
}
