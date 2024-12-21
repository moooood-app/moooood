<?php

namespace App\Scheduler;

use App\Message\Awards\ProcessWeeklySentimentAwards;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('awards')]
final readonly class AwardsScheduler implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
        private LockFactory $lockFactory,
    ) {
    }

    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->add(
                RecurringMessage::cron('0 1 * * 1', new ProcessWeeklySentimentAwards()),
            )
            ->stateful($this->cache)
            ->lock($this->lockFactory->createLock('awards'))
        ;
    }
}
