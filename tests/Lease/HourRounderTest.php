<?php

declare(strict_types=1);

namespace SlaveMarket\Lease;

use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

class HourRounderTest extends TestCase
{
    public function testImmutableObject(): void
    {
        $rounder = new HourRounder();
        $dataTime = '2020-01-01 15:30:00';
        $dataTimeObject = new \DateTime($dataTime);

        $rounder->round($dataTimeObject, true);
        assertEquals($dataTimeObject, new \DateTime($dataTime));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testRound(\DateTimeInterface $dateTime, bool $downRound, \DateTimeInterface $expected): void
    {
        $rounder = new HourRounder();
        assertEquals($expected, $rounder->round($dateTime, $downRound));
    }

    public function dataProvider(): iterable
    {
        yield 'вниз' => [
            new \DateTime('2020-01-01 15:30:00'),
            true,
            new \DateTime('2020-01-01 15:00:00')
        ];

        yield 'вверх' => [
            new \DateTimeImmutable('2020-01-01 15:30:00'),
            false,
            new \DateTimeImmutable('2020-01-01 16:00:00')
        ];

        yield 'вниз, но округлять нечего' => [
            new \DateTimeImmutable('2020-01-01 15:00:00'),
            true,
            new \DateTimeImmutable('2020-01-01 15:00:00')
        ];

        yield 'вверх, но округлять нечего' => [
            new \DateTimeImmutable('2020-01-01 15:00:00'),
            false,
            new \DateTimeImmutable('2020-01-01 15:00:00')
        ];
    }
}