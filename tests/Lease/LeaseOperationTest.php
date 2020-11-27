<?php

namespace SlaveMarket\Lease;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use SlaveMarket\Master;
use SlaveMarket\MastersRepository;
use SlaveMarket\Slave;
use SlaveMarket\SlavesRepository;

/**
 * Тесты операции аренды раба
 *
 * @package SlaveMarket\Lease
 */
class LeaseOperationTest extends TestCase
{
    use ProphecyTrait;

    /**
     * Stub репозитория хозяев
     */
    private function makeFakeMasterRepository(Master ...$masters): MastersRepository
    {
        $mastersRepository = $this->prophesize(MastersRepository::class);
        foreach ($masters as $master) {
            $mastersRepository->getById($master->getId())->willReturn($master);
        }

        return $mastersRepository->reveal();
    }

    /**
     * Stub репозитория рабов
     */
    private function makeFakeSlaveRepository(Slave ...$slaves): SlavesRepository
    {
        $slavesRepository = $this->prophesize(SlavesRepository::class);
        foreach ($slaves as $slave) {
            $slavesRepository->getById($slave->getId())->willReturn($slave);
        }

        return $slavesRepository->reveal();
    }

    /**
     * Если раб занят, то арендовать его не получится
     */
    public function testPeriodIsBusyFailedWithOverlapInfo()
    {
        // -- Arrange
        {
            // Хозяева
            $master1    = new Master(1, 'Господин Боб');
            $master2    = new Master(2, 'сэр Вонючка');
            $masterRepo = $this->makeFakeMasterRepository($master1, $master2);

            // Раб
            $slave1    = new Slave(1, 'Уродливый Фред', 20);
            $slaveRepo = $this->makeFakeSlaveRepository($slave1);

            // Договор аренды. 1й хозяин арендовал раба
            $leaseContract1 = new LeaseContract($master1, $slave1, 80, [
                new LeaseHour(new \DateTimeImmutable('2017-01-01 01:00:00')),
                new LeaseHour(new \DateTimeImmutable('2017-01-01 02:00:00')),
                new LeaseHour(new \DateTimeImmutable('2017-01-01 03:00:00')),
                new LeaseHour(new \DateTimeImmutable('2017-01-01 04:00:00')),
            ]);

            // Stub репозитория договоров
            $contractsRepo = $this->prophesize(LeaseContractsRepository::class);
            $contractsRepo
                ->getForSlave($slave1->getId(), new \DateTimeImmutable('2017-01-01'), new \DateTimeImmutable('2017-01-02'))
                ->willReturn([$leaseContract1]);

            // Запрос на новую аренду. 2й хозяин выбрал занятое время
            $leaseRequest = new LeaseRequest(
                $master2->getId(),
                $slave1->getId(),
                new \DateTimeImmutable('2017-01-01 01:30:00'),
                new \DateTimeImmutable('2017-01-01 02:01:00')
            );

            // Операция аренды
            $leaseOperation = new LeaseOperation($contractsRepo->reveal(), $masterRepo, $slaveRepo);
        }

        // -- Act
        $response = $leaseOperation->run($leaseRequest);

        // -- Assert
        $expectedErrors = ['Ошибка. Раб #1 "Уродливый Фред" занят. Занятые часы: "2017-01-01 01:00:00", "2017-01-01 03:00:00"'];

        $this->assertEquals($expectedErrors, $response->getErrors());
        $this->assertNull($response->getLeaseContract());
    }

    /**
     * Если раб бездельничает, то его легко можно арендовать
     */
    public function testIdleSlaveSuccessFullyLeased ()
    {
        // -- Arrange
        {
            // Хозяева
            $master1    = new Master(1, 'Господин Боб');
            $masterRepo = $this->makeFakeMasterRepository($master1);

            // Раб
            $slave1    = new Slave(1, 'Уродливый Фред', 20);
            $slaveRepo = $this->makeFakeSlaveRepository($slave1);

            $contractsRepo = $this->prophesize(LeaseContractsRepository::class);
            $contractsRepo
                ->getForSlave($slave1->getId(), '2017-01-01', '2017-01-01')
                ->willReturn([]);

            // Запрос на новую аренду
            $leaseRequest           = new LeaseRequest(
                $master1->getId(),
                $slave1->getId(),
                new \DateTimeImmutable('2017-01-01 01:30:00'),
                new \DateTimeImmutable('2017-01-01 02:01:00'),
            );

            // Операция аренды
            $leaseOperation = new LeaseOperation($contractsRepo->reveal(), $masterRepo, $slaveRepo);
        }

        // -- Act
        $response = $leaseOperation->run($leaseRequest);

        // -- Assert
        $this->assertEmpty($response->getErrors());
        $this->assertInstanceOf(LeaseContract::class, $response->getLeaseContract());
        $this->assertEquals(40, $response->getLeaseContract()->price);
    }
}