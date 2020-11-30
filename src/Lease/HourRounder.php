<?php

declare(strict_types=1);

namespace SlaveMarket\Lease;

/**
 * Округляет время до часов
 */
class HourRounder
{
    public function round(\DateTimeInterface $dateTime, bool $down): \DateTimeInterface
    {
        if ($dateTime instanceof \DateTime) {
            $dateTime = clone $dateTime;
        }

        // todo: учёт секунд
        if (!$minutes = (int)$dateTime->format('i')) {
            return $dateTime;
        }

        $operation = 'sub';
        if (!$down) {
            $operation = 'add';
            $minutes = 60 - $minutes;
        }

        $interval = new \DateInterval(sprintf('PT%dM', $minutes));
        return $dateTime->$operation($interval);
    }
}