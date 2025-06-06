<?php

namespace App\DataFixtures\Helpers\Metadata;

use App\Entity\EntryMetadata;
use Faker\Generator;

interface MetadataHelperInterface
{
    public function __construct(Generator $faker);

    public function provideMetadata(): EntryMetadata;
}
