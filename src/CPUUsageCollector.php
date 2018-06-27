<?php

namespace WyriHaximus\React\Inspector\CPUUsage;

use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use function React\Promise\all;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;
use function WyriHaximus\React\childProcessPromise;
use WyriHaximus\React\ProcessOutcome;

final class CPUUsageCollector
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var int
     */
    private $pid;

    /**
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
        $this->pid = getmypid();
    }

    public function collect(): PromiseInterface
    {
        return all([
            'cpu.usage' => childProcessPromise($this->loop, new Process('ps -p ' . $this->pid . ' -o "pcpu" -S'))->then(function (ProcessOutcome $outcome) {
                list(, $cpuUsage) = explode(PHP_EOL, $outcome->getStdout());
                return resolve([
                    'value' => (float)$cpuUsage,
                    'time' => time(),
                ]);
            })
        ]);
    }
}
