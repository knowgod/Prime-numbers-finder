<?php

namespace Knowgod\Prime;

use Amp\Future;
use Amp\Parallel\Worker;
use Amp\TimeoutCancellation;
use Knowgod\Prime\IsPrime\Task;

class Finder
{
    private const CONCURRENCY = 16;
    private const FILE_PATH = 'data/numbers.csv';

    private array $found = [1 => 2, 3];

    /**
     * @var \Knowgod\Prime\SaveTo\File
     */
    private SaveTo\File $saver;

    public function __construct()
    {
        $this->saver = new SaveTo\File(self::FILE_PATH);
    }

    public function __destruct()
    {
        $this->saver->append($this->found);
    }

    public function findNumbers(int $count): array
    {
        $startWith = $this->getLastFoundItem() + 2;
        while (count($this->found) < $count) {
            $this->findNumbersInRange($startWith, $startWith + 2 * self::CONCURRENCY);
            $startWith = end($this->found) + 2;
        }
        return $this->found;
    }

    /**
     * @return int
     */
    private function getLastFoundItem(): int
    {
        $lastFound = end($this->found);
        try {
            $fromFile = $this->saver->readLast();
            if (null !== $fromFile) {
                $lastFound = $fromFile;
                $this->found = [];
            }
        } catch (\Throwable $e) {
        }
        return $lastFound;
    }

    public function findNumbersInRange(int $start, int $finish): void
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
//            \printf("Number %d is %s\n", $checked, ($response ? 'prime' : 'not prime'));
            if ($response ?? false) {
                $this->found[] = $checked;
            }
        }
    }
}
