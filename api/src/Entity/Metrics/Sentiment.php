<?php

declare(strict_types=1);

namespace App\Entity\Metrics;

use App\Enum\Processor;
use App\Metadata\Metrics\MetricsApiResource;
use App\Repository\Metrics\SentimentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SentimentRepository::class)]
#[MetricsApiResource(metricsType: Processor::SENTIMENT)]
class Sentiment implements MetricsIdentifierInterface
{
    use MetricsIdentifierTrait;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    public float $positive;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    public float $neutral;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    public float $negative;

    #[ORM\Column(type: Types::FLOAT, precision: 6, scale: 2)]
    public float $compound;
}
