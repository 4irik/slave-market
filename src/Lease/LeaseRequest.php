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
    /** Id хозяина */
    protected int $masterId;

    /** ID раба */
    protected int $slaveId;

    /** время начала работ */
    protected \DateTimeImmutable $timeFrom;

    /** время окончания работ */
    protected \DateTimeImmutable $timeTo;

    public function __construct(int $masterId, int $slaveId, \DateTimeImmutable $timeFrom, \DateTimeImmutable $timeTo)
    {
        $this->masterId = $masterId;
        $this->slaveId = $slaveId;
        $this->timeFrom = $timeFrom;
        $this->timeTo = $timeTo;
    }

    public function getMasterId(): int
    {
        return $this->masterId;
    }

    public function getSlaveId(): int
    {
        return $this->slaveId;
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