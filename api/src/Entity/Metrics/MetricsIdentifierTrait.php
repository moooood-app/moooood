<?php

declare(strict_types=1);

namespace App\Entity\Metrics;

use App\Enum\Metrics\GroupingCriteria;
use Doctrine\ORM\Mapping as ORM;

trait MetricsIdentifierTrait
{
    #[ORM\Column(type: 'string')]
    private string $id;

    #[ORM\Id]
    #[ORM\Column(type: 'string', enumType: GroupingCriteria::class)]
    private GroupingCriteria $grouping;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getGrouping(): GroupingCriteria
    {
        return $this->grouping;
    }

    public function setGrouping(GroupingCriteria $grouping): static
    {
        $this->grouping = $grouping;

        return $this;
    }
}
