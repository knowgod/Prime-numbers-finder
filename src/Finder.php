<?php

namespace Primitive;

use Amp\Future;
use Amp\Parallel\Worker;
use Primitive\IsPrimitive\Task;

class Finder
{
    private array $found = [1 => 1, 2, 3];

    public function findNumbers(int $finish, ?int $start = null): array
    {
        $start  = $start ?? end($this->found) + 2;
        $finish = $finish % 2 == 0 ? $finish + 1 : $finish;

        $executions = [];
        for ($checkNumber = $start; $checkNumber <= $finish; $checkNumber += 2) {
            $executions[$checkNumber] = Worker\submit(new Task($checkNumber));
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
            if ($response) {
                $this->found[] = $checked;
            }
        }

        return $this->found;
    }
}
