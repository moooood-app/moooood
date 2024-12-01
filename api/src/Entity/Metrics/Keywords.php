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
    private array $keywords = [];

    /**
     * @return list<array{average_score: float, count: int}>
     */
    public function getKeywords(): array
    {
        usort($this->keywords, static function (array $a, array $b): int {
            return $b['count'] <=> $a['count'] ?: $b['average_score'] <=> $a['average_score'];
        });

        return array_slice($this->keywords, 0, 25, true);
    }

    /**
     * @param list<array{average_score: float, count: int}> $keywords
     */
    public function setKeywords(array $keywords): void
    {
        $this->keywords = $keywords;
    }
}
