<?php

declare(strict_types=1);

namespace SlaveMarket\Lease;

class LeasePeriod implements \IteratorAggregate
{
    protected \DatePeriod $period;

    public function __construct(\DateTimeInterface $from, \DateTimeInterface $to)
    {
        $from = $this->toImmutable($from);
        $to = $this->toImmutable($to);

        $intervalFormat = $to->diff($from)->days > 0 ? 'P1D' : 'PT1H';
        $interval = new \DateInterval($intervalFormat);

        $this->period = new \DatePeriod($from, $interval, $to->add($interval));
    }

    protected function toImmutable(\DateTimeInterface $dateTime): \DateTimeImmutable
    {
        if ($dateTime instanceof \DateTimeImmutable) {
            return $dateTime;
        }

        /** @var \DateTime $dateTime */
        return \DateTimeImmutable::createFromMutable($dateTime);
    }

    /**
     * @return \DateTimeImmutable[]
     */
    public function getIterator(): \Traversable
    {
        return $this->period;
    }
}