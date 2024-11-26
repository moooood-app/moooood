<?php

namespace App\Dto\Metrics;

use App\Enum\Metrics\GroupingCriteria;
use App\Enum\Processor;
use App\Metadata\Metrics\MetricsApiResource;
use Symfony\Component\HttpFoundation\InputBag;

final class MetricsQuery
{
    private function __construct(
        public readonly GroupingCriteria $groupingCriteria,
        public readonly \DateTimeImmutable $dateFrom,
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
        $groupingCriteria = GroupingCriteria::from($grouping);

        /** @var string */
        $groupByParts = $query->get(MetricsApiResource::GROUP_BY_PARTS_FILTER_KEY);
        $groupByParts = \in_array($groupByParts, [true, 'true', '1'], true);

        /** @var string */
        $dateFrom = $query->get(MetricsApiResource::FROM_DATE_FILTER_KEY) ?? 'now';
        $dateFrom = new \DateTime($dateFrom);

        switch ($groupingCriteria) {
            case GroupingCriteria::ENTRY:
            case GroupingCriteria::HOUR:
                $dateFrom->modify('last Sunday')->modify('+1 day');
                break;
            case GroupingCriteria::DAY:
                $dateFrom->modify('first day of this month');
                break;
            case GroupingCriteria::WEEK:
                $month = (int) $dateFrom->format('n');
                $startMonth = 1 + 3 * floor(($month - 1) / 3);
                $dateFrom->setDate((int) $dateFrom->format('Y'), (int) $startMonth, 1);
                break;
            case GroupingCriteria::MONTH:
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
            GroupingCriteria::ENTRY, GroupingCriteria::HOUR => $dateUntil->modify('+1 week'),
            GroupingCriteria::DAY => $dateUntil->modify('+1 month'),
            GroupingCriteria::WEEK => $dateUntil->modify('+3 months'),
            GroupingCriteria::MONTH => $dateUntil->modify('+1 year'),
        };

        $dateUntil->setTime(0, 0, 0, 0);

        return \DateTimeImmutable::createFromMutable($dateUntil);
    }
}
