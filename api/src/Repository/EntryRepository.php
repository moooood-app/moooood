<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Entry;
use App\Enum\Processor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Entry>
 */
class EntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entry::class);
    }

    public function removeExistingMetadataForProcessor(Entry $entry, Processor $processor): void
    {
        $entityManager = $this->getEntityManager();
        foreach ($entry->getMetadata() as $metadata) {
            if ($metadata->getProcessor() !== $processor) {
                continue;
            }
            $entry->removeMetadata($metadata);
            $entityManager->remove($metadata);
        }
        $entityManager->flush();
    }
}
