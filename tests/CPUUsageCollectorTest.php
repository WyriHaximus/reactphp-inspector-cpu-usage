<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\Inspector\CPUUsage;

use ApiClients\Tools\TestUtilities\TestCase;
use React\EventLoop\Factory;
use Rx\React\Promise;
use WyriHaximus\React\Inspector\CPUUsage\CPUUsageCollector;
use WyriHaximus\React\Inspector\Metric;

final class CPUUsageCollectorTest extends TestCase
{
    public function testCollect()
    {
        $loop = Factory::create();

        $cpuUsageCollector = new CPUUsageCollector($loop);

        for ($i = 0; $i < 10000000; $i++) {
            // Cause some CPU usage
        }

        $begin = microtime(true);
        /** @var Metric $metric */
        $metric = $this->await(Promise::fromObservable($cpuUsageCollector->collect()), $loop, 10);
        $end = microtime(true);

        self::assertSame('cpu.usage', $metric->getKey());
        self::assertTrue($metric->getValue() > 0.0);
        self::assertTrue($metric->getTime() >= $begin && $metric->getTime() <= $end);
    }
}
