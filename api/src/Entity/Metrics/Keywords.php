<?php

declare(strict_types=1);

namespace App\Entity\Metrics;

use App\Enum\Processor;
use App\Metadata\Metrics\MetricsQueryParameter;
use App\Metadata\Metrics\ProcessorMetricsApiResource;
use App\Repository\Metrics\KeywordsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: KeywordsRepository::class)]
#[ProcessorMetricsApiResource(metricsProcessor: Processor::KEYWORDS)]
#[MetricsQueryParameter]
class Keywords implements MetricsIdentifierInterface
{
    use MetricsIdentifierTrait;

    /**
     * @var list<array{average_score: float, count: int}>
     */
    #[ORM\Column(type: Types::JSON)]
    public array $keywords = [];
}
