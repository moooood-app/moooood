<?php

declare(strict_types=1);

namespace App\Entity\Metrics;

use App\Enum\Metrics\GroupingCriteria;
use Doctrine\ORM\Mapping as ORM;

trait MetricsIdentifierTrait
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    public string $id;

    #[ORM\Column(type: 'string', enumType: GroupingCriteria::class)]
    public GroupingCriteria $grouping;
}
