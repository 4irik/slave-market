<?php

namespace SlaveMarket\Lease;

use SlaveMarket\Master;
use SlaveMarket\Slave;

/**
 * Договор аренды
 *
 * @package SlaveMarket\Lease
 */
class LeaseContract
{
    /** Хозяин */
    protected Master $master;

    /** Раб */
    protected Slave $slave;

    /** Стоимость */
    protected float $price = 0;

    /** Список арендованных часов */
    protected LeasePeriod $leasePeriod;

    public function __construct(Master $master, Slave $slave, float $price, LeasePeriod $leasePeriod)
    {
        $this->master = $master;
        $this->slave = $slave;
        $this->price = $price;
        $this->leasePeriod = $leasePeriod;
    }

    public function getMaster(): Master
    {
        return $this->master;
    }

    public function getSlave(): Slave
    {
        return $this->slave;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getLeasePeriod(): LeasePeriod
    {
        return $this->leasePeriod;
    }
}
