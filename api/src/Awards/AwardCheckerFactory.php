<?php

namespace App\Awards;

use App\Awards\Contracts\AwardCheckerInterface;
use App\Awards\Contracts\ChainableAwardCheckerInterface;
use App\Entity\User;
use App\Enum\AwardType;
use App\Repository\Awards\AwardRepository;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Contracts\Cache\CacheInterface;

final class AwardCheckerFactory
{
    /**
     * @var array<string, AwardCheckerInterface>
     */
    private array $checkers;

    private CacheInterface $chains;

    /**
     * @param iterable<AwardCheckerInterface> $checkers
     */
    public function __construct(
        #[AutowireIterator(tag: AwardCheckerInterface::CHECKER_TAG)]
        iterable $checkers,
        private readonly AwardRepository $awardRepository,
    ) {
        $this->checkers = [];
        foreach ($checkers as $checker) {
            if (null !== ($this->checkers[$checker::getSupportedType()->value] ?? null)) {
                throw new \InvalidArgumentException(\sprintf('Checker for type %s already exists', $checker::getSupportedType()->value));
            }

            $this->checkers[$checker::getSupportedType()->value] = $checker;
            $this->chains = new ArrayAdapter(storeSerialized: false);
        }
    }

    public function create(User $user, AwardType $type): ?AwardCheckerInterface
    {
        return $this->chains->get(
            \sprintf('user_%s-type_%s', $user->getId()->toString(), $type->value),
            function () use ($user, $type): ?AwardCheckerInterface {
                /** @var \SplPriorityQueue<int, ChainableAwardCheckerInterface> */
                $chain = new \SplPriorityQueue();
                $chain->setExtractFlags(\SplPriorityQueue::EXTR_DATA);

                $nonGrantedAwards = $this->awardRepository->findNonGrantedAwards($user, $type);

                foreach ($nonGrantedAwards as $award) {
                    $checker = $this->checkers[$award->getType()->value] ?? null;

                    if (null === $checker) {
                        continue;
                    }

                    $checker = $checker->withAward($award);

                    if (!$checker instanceof ChainableAwardCheckerInterface) {
                        return $checker;
                    }

                    $chain->insert($checker, 0 - $award->getPriority());
                }

                if ($chain->isEmpty()) {
                    return null;
                }

                // Clone the chain for iteration
                $clonedChain = clone $chain;
                $previous = null;

                foreach ($clonedChain as $checker) {
                    if ($previous instanceof ChainableAwardCheckerInterface) {
                        $previous->setNext($checker);
                    }
                    $previous = $checker;
                }

                return $chain->top(); // @phpstan-ignore-line
            }
        );
    }
}
