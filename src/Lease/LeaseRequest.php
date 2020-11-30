<?php

namespace SlaveMarket\Lease;

use SlaveMarket\Master;
use SlaveMarket\Slave;

/**
 * Запрос на аренду раба
 *
 * @package SlaveMarket\Lease
 */
class LeaseRequest
{
    protected Master $master;

    protected Slave $slave;

    /** время начала работ */
    protected \DateTimeImmutable $timeFrom;

    /** время окончания работ */
    protected \DateTimeImmutable $timeTo;

    public function __construct(Master $master, Slave $slave, \DateTimeImmutable $timeFrom, \DateTimeImmutable $timeTo)
    {
        $this->master = $master;
        $this->slave = $slave;
        $this->timeFrom = $timeFrom;
        $this->timeTo = $timeTo;
    }

    public function getMaster(): Master
    {
        return $this->master;
    }

    public function getSlave(): Slave
    {
        return $this->slave;
    }

    public function getTimeFrom(): \DateTimeImmutable
    {
        return $this->timeFrom;
    }

    public function getTimeTo(): \DateTimeImmutable
    {
        return $this->timeTo;
    }
}