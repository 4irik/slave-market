<?php

namespace SlaveMarket\Lease;

/**
 * Репозиторий договоров аренды
 *
 * @package SlaveMarket\Lease
 */
interface LeaseContractsRepository
{
    /**
     * Возвращает список договоров аренды для раба, в которых заняты часы из указанного периода
     *
     * @param int $slaveId
     * @param \DateTimeImmutable  $dateFrom
     * @param \DateTimeImmutable  $dateTo
     * @return LeaseContract[]
     */
    public function getForSlave(int $slaveId, \DateTimeImmutable $dateFrom, \DateTimeImmutable  $dateTo) : array;
}