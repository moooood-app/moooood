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
    private array $keywords = [];

    /**
     * @return list<array{average_score: float, count: int}>
     */
    public function getKeywords(): array
    {
        $keywords = $this->keywords;

        usort($keywords, static function ($a, $b) {
            if ($a['count'] !== $b['count']) {
                return $b['count'] <=> $a['count']; // Descending order
            }

            return $b['average_score'] <=> $a['average_score']; // Descending order
        });

        return $keywords;
    }

    /**
     * @param list<array{average_score: float, count: int}> $keywords
     */
    public function setKeywords(array $keywords): static
    {
        $this->keywords = $keywords;

        return $this;
    }
}
