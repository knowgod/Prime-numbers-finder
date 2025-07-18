<?php

namespace Primitive\IsPrimitive;

use Amp\Cancellation;
use Amp\Sync\Channel;

class Task implements \Amp\Parallel\Worker\Task
{
    public function __construct(private readonly int $checkNumber)
    {
    }

    /**
     * @inheritDoc
     */
    public function run(Channel $channel, Cancellation $cancellation): ?bool
    {
        $checkNumber = $this->checkNumber;
        for ($i = 2; $i < ceil($checkNumber / 2); $i++) {
            if ($checkNumber % $i == 0) {
                return false;
            }
            if ($cancellation->isRequested()) {
                return null;
            }
        }
        return true;
    }
}
