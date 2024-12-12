<?php

namespace App\Repository\Metrics;

use App\Enum\Metrics\MetricsGrouping;
use App\Enum\Processor;
use App\Metadata\Metrics\MetricsApiResource;
use Symfony\Component\HttpFoundation\InputBag;

final class MetricsQuery
{
    private function __construct(
        public readonly MetricsGrouping $groupingCriteria,
        private readonly \DateTimeImmutable $dateFrom,
        public ?Processor $processor = null,
        public bool $groupByParts = false,
    ) {
    }

    /**
     * @param InputBag<bool|float|int|string> $query
     */
    public static function fromInputBag(InputBag $query): self
    {
        /** @var string */
        $grouping = $query->get(MetricsApiResource::GROUPING_FILTER_KEY);
        $groupingCriteria = MetricsGrouping::from($grouping);

        /** @var string */
        $groupByParts = $query->get(MetricsApiResource::GROUP_BY_PARTS_FILTER_KEY);
        $groupByParts = \in_array($groupByParts, [true, 'true', '1'], true);

        /** @var string */
        $dateFrom = $query->get(MetricsApiResource::FROM_DATE_FILTER_KEY) ?? 'now';
        $dateFrom = new \DateTime($dateFrom);

        switch ($groupingCriteria) {
            case MetricsGrouping::ENTRY:
            case MetricsGrouping::HOUR:
            case MetricsGrouping::DAY:
                // do nothing
                break;
            case MetricsGrouping::WEEK:
                $dayOfWeek = (int) $dateFrom->format('N');
                if (1 === $dayOfWeek) {
                    break;
                }
                $daysToSubtract = $dayOfWeek - 1; // Subtract the days since the last Monday
                $dateFrom->modify("-{$daysToSubtract} days");
                break;
            case MetricsGrouping::MONTH:
                $dateFrom->modify('first day of January');
                break;
        }

        $dateFrom->setTime(0, 0, 0, 0);

        $dateFrom = \DateTimeImmutable::createFromMutable($dateFrom);

        return new self(
            groupingCriteria: $groupingCriteria,
            dateFrom: $dateFrom,
            groupByParts: $groupByParts,
        );
    }

    public function withProcessor(Processor $processor): self
    {
        $query = clone $this;
        $query->processor = $processor;

        return $query;
    }

    public function getDateFrom(): \DateTimeImmutable
    {
        return $this->dateFrom;
    }

    public function getDateUntil(): \DateTimeImmutable
    {
        $dateUntil = \DateTime::createFromImmutable($this->dateFrom);

        match ($this->groupingCriteria) {
            MetricsGrouping::ENTRY, MetricsGrouping::HOUR => $dateUntil->modify('+1 week'),
            MetricsGrouping::DAY => $dateUntil->modify('+1 month'),
            MetricsGrouping::WEEK => $dateUntil->modify('+4 weeks'),
            MetricsGrouping::MONTH => $dateUntil->modify('+1 year'),
        };

        $dateUntil->setTime(0, 0, 0, 0);

        return \DateTimeImmutable::createFromMutable($dateUntil);
    }
}
