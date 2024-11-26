<?php

declare(strict_types=1);

namespace App\Entity\Metrics;

use ApiPlatform\Metadata\ApiProperty;
use App\Enum\Processor;
use App\Metadata\Metrics\MetricsApiResource;
use App\Repository\Metrics\KeywordsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

#[ORM\MappedSuperclass(repositoryClass: KeywordsRepository::class)]
#[MetricsApiResource(metricsType: Processor::KEYWORDS)]
class Keywords implements MetricsIdentifierInterface
{
    use MetricsPropertiesTrait;

    /**
     * @var list<array{average_score: float, count: int}>
     */
    #[ORM\Column(type: Types::JSON)]
    #[ApiProperty(description: 'The average score and count of detected keywords.')]
    #[Serializer\Groups([MetricsApiResource::METRICS_NORMALIZATION_GROUP])]
    public array $keywords = [];
}
