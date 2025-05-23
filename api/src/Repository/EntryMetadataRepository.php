<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Entry;
use App\Entity\EntryMetadata;
use App\Message\ProcessorOutputMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EntryMetadata>
 */
class EntryMetadataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntryMetadata::class);
    }

    public function createMetadataFromProcessorOutput(Entry $entry, ProcessorOutputMessage $processorOutputMessage): void
    {
        $metadata = new EntryMetadata();
        $metadata->setEntry($entry);
        $metadata->setProcessor($processorOutputMessage->processor);
        $metadata->setMetadata($processorOutputMessage->result);

        $this->getEntityManager()->persist($metadata);
        $this->getEntityManager()->flush();
    }
}
