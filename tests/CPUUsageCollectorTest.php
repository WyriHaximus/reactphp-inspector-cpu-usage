<?php

namespace WyriHaximus\React\Tests\Inspector\CPUUsage;

use ApiClients\Tools\TestUtilities\TestCase;
use React\EventLoop\Factory;
use WyriHaximus\React\Inspector\CPUUsage\CPUUsageCollector;

final class CPUUsageCollectorTest extends TestCase
{
    public function testCollect()
    {
        $begin = time();
        $loop = Factory::create();

        $cpuUsageCollector = new CPUUsageCollector($loop);

        for ($i = 0; $i < 10000000; $i++) {
            // Cause some CPU usage
        }

        $result = $this->await($cpuUsageCollector->collect(), $loop, 10);
        $end = time();

        self::assertCount(1, $result);
        self::assertTrue(isset($result['cpu.usage']));
        self::assertCount(2, $result['cpu.usage']);
        self::assertTrue(isset($result['cpu.usage']['value']));
        self::assertTrue($result['cpu.usage']['value'] >= 0 && $result['cpu.usage']['value'] <= 100);
        self::assertTrue(isset($result['cpu.usage']['time']));
        self::assertTrue($result['cpu.usage']['time'] >= $begin && $result['cpu.usage']['time'] <= $end);
    }
}
