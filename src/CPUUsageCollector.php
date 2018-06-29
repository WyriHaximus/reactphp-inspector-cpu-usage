<?php declare(strict_types=1);

namespace WyriHaximus\React\Inspector\CPUUsage;

use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use Rx\Observable;
use Rx\ObservableInterface;
use WyriHaximus\React\Inspector\CollectorInterface;
use WyriHaximus\React\Inspector\Metric;
use WyriHaximus\React\ProcessOutcome;
use function React\Promise\resolve;
use function WyriHaximus\React\childProcessPromise;

final class CPUUsageCollector implements CollectorInterface
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var int
     */
    private $pid;

    private $promises = [];

    /**
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
        $this->pid = getmypid();
    }

    public function collect(): ObservableInterface
    {
        $promise = childProcessPromise($this->loop, new Process('ps -p ' . $this->pid . ' -o "pcpu" -S'));
        $hash = spl_object_hash($promise);
        $this->promises[$hash] = $promise;

        return Observable::fromPromise(
            $promise->then(function (ProcessOutcome $outcome) use ($hash) {
                unset($this->promises[$hash]);
                list(, $cpuUsage) = explode(PHP_EOL, $outcome->getStdout());

                return resolve(new Metric(
                    'cpu.usage',
                    (float)$cpuUsage
                ));
            })
        );
    }

    public function cancel(): void
    {
        foreach ($this->promises as $hash => $promise) {
            $promise->cancel();
            unset($this->promises[$hash]);
        }
        unset($this->loop, $this->pid);
    }
}
