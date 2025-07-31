<?php

namespace Knowgod\Prime;

use Amp\Future;
use Amp\Parallel\Worker;
use Amp\TimeoutCancellation;
use Knowgod\Prime\IsPrime\Task;

class Finder
{
    private const CONCURRENCY = 16;

    private array $found = [1 => 1, 2, 3];

    public function findNumbers(int $count): array
    {
        while (count($this->found) < $count) {
            $startWith = end($this->found) + 2;
            $this->findNumbersInRange($startWith, $startWith + 2 * self::CONCURRENCY);
        }
        return $this->found;
    }

    public function findNumbersInRange(int $start, int $finish): array
    {
        $finish = $finish % 2 == 0 ? ++$finish : $finish;
        $start  = $start % 2 == 0 ? --$start : $start;
        if ($start < 3) {
            $start = 3;
        }

        $executions = [];
        for ($checkNumber = $start; $checkNumber <= $finish; $checkNumber += 2) {
            $executions[$checkNumber] = Worker\submit(new Task($checkNumber), new TimeoutCancellation(0.5));
        }

        $responses = Future\await(
            array_map(
                function (Worker\Execution $e) {
                    return $e->getFuture();
                },
                $executions,
            ),
        );

        foreach ($responses as $checked => $response) {
//            \printf("Number %d is %s\n", $checked, ($response ? 'primitive' : 'not primitive'));
            if ($response ?? false) {
                $this->found[] = $checked;
            }
        }

        return $this->found;
    }
}
