<?php

namespace SlaveMarket\Lease;

use SlaveMarket\MastersRepository;
use SlaveMarket\Slave;
use SlaveMarket\SlavesRepository;

/**
 * Операция "Арендовать раба"
 *
 * @package SlaveMarket\Lease
 */
class LeaseOperation
{
    protected LeaseContractsRepository $contractsRepository;
    protected MastersRepository $mastersRepository;
    protected SlavesRepository $slavesRepository;
    protected HourRounder $hourRounder;

    public function __construct(LeaseContractsRepository $contractsRepo, HourRounder $hourRounder)
    {
        $this->contractsRepository = $contractsRepo;
        $this->hourRounder = $hourRounder;
    }

    /**
     * Выполнить операцию
     */
    public function run(LeaseRequest $request): LeaseResponse
    {
        // todo: заменить преобразование до начала и конца дня на округление
        $from = $this->hourRounder->round($request->getTimeFrom(), true);
        $to = $this->hourRounder->round($request->getTimeTo(), false);
        $leaseContracts = $this->contractsRepository->getForSlave($request->getSlave()->getId(), $from, $to);

        $response = new LeaseResponse();
        if ($overlap = $this->findOverlap($leaseContracts, $from, $to)) {
            $response->addError(sprintf(
                'Ошибка. Раб #%d "%s" занят. Занятые часы: "%s", "%s"',
                $request->getSlave()->getId(),
                $request->getSlave()->getName(),
                $overlap['from']->format('Y-m-d H:i:s'),
                $overlap['to']->format('Y-m-d H:i:s')
            ));

            return $response;
        }

        $leasePeriod = new LeasePeriod($from, $to);
        $response->setLeaseContract(new LeaseContract(
            $request->getMaster(),
            $request->getSlave(),
            $this->calculateContractPrice($request->getSlave(), $leasePeriod),
            $leasePeriod
        ));

        return $response;
    }

    protected function calculateContractPrice(Slave $slave, LeasePeriod $period): float
    {
        return $slave->getPricePerHour() * (iterator_count($period) - 1);
    }

    /**
     * Проверяет на наличие пересечений между арендованными часами и теми что хотят арендовать
     * @return null|array = [
     *   'from' => \DateTimeInterface,
     *   'to' => \DateTimeInterface,
     * ]
     * @var \DateTimeInterface $from
     * @var \DateTimeInterface $to
     * @var LeaseContract[] $leaseContracts
     * @todo: проверить как это будем работать при мешанине из дней и часов
     */
    protected function findOverlap(iterable $leaseContracts, \DateTimeInterface $from, \DateTimeInterface $to): ?array
    {
        $allPeriods = new \AppendIterator();
        foreach ($leaseContracts as $contract) {
            /** @var LeaseContract $contract */
            $allPeriods->append(new \IteratorIterator($contract->getLeasePeriod()));
        }

        $hasOverlap = false;
        $endOverlap = null;
        foreach ($allPeriods as $leaseDateTime) {
            // так потихоньку подбираем время начального пересечения
            $hasOverlap = $hasOverlap ?: $from <= $leaseDateTime;

            // начальное пересечение есть, подбираем конечное
            if ($hasOverlap && $to >= $leaseDateTime) {
                $endOverlap = $leaseDateTime;
            }

            // конечно пересечение подобрано, дальше итерировать нет смысла
            if($hasOverlap && $to < $leaseDateTime) {
                break;
            }
        }

        return $hasOverlap ? ['from' => $from, 'to' => $endOverlap] : null;
    }
}