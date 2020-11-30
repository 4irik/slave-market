<?php

namespace SlaveMarket\Lease;

use PHPUnit\Framework\TestCase;
use SlaveMarket\Master;
use SlaveMarket\Slave;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertNull;

/**
 * Тесты операции аренды раба
 *
 * @package SlaveMarket\Lease
 */
class LeaseOperationTest extends TestCase
{
    /**
     * Если раб занят, то арендовать его не получится
     */
    public function testPeriodIsBusyFailedWithOverlapInfo()
    {
        // -- Arrange
        {
            // Хозяева
            $master1 = new Master(1, 'Господин Боб');
            $master2 = new Master(2, 'сэр Вонючка');
            // Раб
            $slave1 = new Slave(1, 'Уродливый Фред', 20);

            // Договор аренды. 1й хозяин арендовал раба
            $leaseContract1 = new LeaseContract(
                $master1,
                $slave1,
                80,
                new LeasePeriod(new \DateTimeImmutable('2017-01-01 00:00:00'), new \DateTimeImmutable('2017-01-01 03:00:00'))
            );

            // Stub репозитория договоров
            $contractsRepo = $this->createMock(LeaseContractsRepository::class);
            $contractsRepo
                ->expects(self::once())
                ->method('getForSlave')
                ->with(
                    $slave1->getId(),
                    new \DateTimeImmutable('2017-01-01 01:00:00'),
                    new \DateTimeImmutable('2017-01-02 03:00:00')
                )
                ->willReturn([$leaseContract1]);

            // Запрос на новую аренду. 2й хозяин выбрал занятое время
            $leaseRequest = new LeaseRequest(
                $master2,
                $slave1,
                new \DateTimeImmutable('2017-01-01 01:30:00'),
                new \DateTimeImmutable('2017-01-02 02:01:00')
            );

            // Операция аренды
            $leaseOperation = new LeaseOperation($contractsRepo, new HourRounder());
        }

        // -- Act
        $response = $leaseOperation->run($leaseRequest);

        // -- Assert
        $expectedErrors = ['Ошибка. Раб #1 "Уродливый Фред" занят. Занятые часы: "2017-01-01 01:00:00", "2017-01-01 03:00:00"'];

        assertEquals($expectedErrors, $response->getErrors());
        assertNull($response->getLeaseContract());
    }

    /**
     * Если раб бездельничает, то его легко можно арендовать
     */
    public function testIdleSlaveSuccessFullyLeased()
    {
        // -- Arrange
        {
            // Хозяева
            $master1 = new Master(1, 'Господин Боб');
            // Раб
            $slave1 = new Slave(1, 'Уродливый Фред', 20);

            $contractsRepo = $this->createMock(LeaseContractsRepository::class);
            $contractsRepo
                ->expects(self::once())
                ->method('getForSlave')
                ->with(
                    $slave1->getId(),
                    new \DateTimeImmutable('2017-01-01 01:00:00'),
                    new \DateTimeImmutable('2017-01-01 03:00:00')
                )
                ->willReturn([]);

            // Запрос на новую аренду
            $leaseRequest = new LeaseRequest(
                $master1,
                $slave1,
                new \DateTimeImmutable('2017-01-01 01:30:00'),
                new \DateTimeImmutable('2017-01-01 02:01:00')
            );

            // Операция аренды
            $leaseOperation = new LeaseOperation($contractsRepo, new HourRounder());
        }

        // -- Act
        $response = $leaseOperation->run($leaseRequest);

        // -- Assert
        assertEmpty($response->getErrors());
        assertInstanceOf(LeaseContract::class, $response->getLeaseContract());
        assertEquals(40, $response->getLeaseContract()->getPrice());
    }
}