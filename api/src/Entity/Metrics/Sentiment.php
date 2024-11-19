<?php

declare(strict_types=1);

namespace App\Entity\Metrics;

use App\Enum\Processor;
use App\Metadata\Metrics\MetricsApiResource;
use App\Metadata\Metrics\MetricsQueryParameter;
use App\Repository\Metrics\SentimentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SentimentRepository::class)]
#[MetricsApiResource(Processor::SENTIMENT)]
#[MetricsQueryParameter]
class Sentiment
{
    use MetricsIdentifierTrait;

    #[ORM\Column(type: 'float', precision: 6, scale: 2)]
    public float $positive;

    #[ORM\Column(type: 'float', precision: 6, scale: 2)]
    public float $neutral;

    #[ORM\Column(type: 'float', precision: 6, scale: 2)]
    public float $negative;

    #[ORM\Column(type: 'float', precision: 6, scale: 2)]
    public float $compound;
}
