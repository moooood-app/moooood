<?php

declare(strict_types=1);

namespace App\Message;

use App\Enum\Processor;

final class ProcessorOutputMessage
{
    private string $entryIri;
    
    /**
     * @var array<mixed>
     */
    private array $result;

    private Processor $processor;

    public function getEntryIri(): string
    {
        return $this->entryIri;
    }

    public function setEntryIri(string $entryIri): void
    {
        $this->entryIri = $entryIri;
    }

    /**
     * @return array<mixed>
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @param array<mixed> $result
     */
    public function setResult(array $result): void
    {
        $this->result = $result;
    }

    public function getProcessor(): Processor
    {
        return $this->processor;
    }

    public function setProcessor(Processor $processor): void
    {
        $this->processor = $processor;
    }
}

