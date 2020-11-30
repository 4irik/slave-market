<?php

declare(strict_types=1);

namespace SlaveMarket\Lease;

use PHPUnit\Framework\TestCase;

class LeasePeriodTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testSuccess(\DateTimeInterface $from, \DateTimeInterface $to, array $expected): void
    {
        $leasePeriod = new LeasePeriod($from, $to);

        $periods = [];
        foreach ($leasePeriod as $period) {
            $periods[] = $period;
        }

        self::assertEquals($expected, $periods);
    }

    public function dataProvider(): iterable
    {
        yield 'часы' => [
            new \DateTime('2020-01-01 00:00:00'),
            new \DateTime('2020-01-01 03:00:00'),
            [
                new \DateTimeImmutable('2020-01-01 00:00:00'),
                new \DateTimeImmutable('2020-01-01 01:00:00'),
                new \DateTimeImmutable('2020-01-01 02:00:00'),
                new \DateTimeImmutable('2020-01-01 03:00:00'),
            ]
        ];

        yield 'дни' => [
            new \DateTime('2020-01-01 00:00:00'),
            new \DateTime('2020-01-03 00:00:00'),
            [
                new \DateTimeImmutable('2020-01-01 00:00:00'),
                new \DateTimeImmutable('2020-01-02 00:00:00'),
                new \DateTimeImmutable('2020-01-03 00:00:00'),
            ]
        ];
    }
}