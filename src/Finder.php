<?php

namespace Primitive;

class Finder
{
    private array $found = [1 => 1, 2, 3];

    public function findNumbers(int $count): array
    {
        $checkNumber = end($this->found) + 2;
        while (count($this->found) < $count) {
            if ($this->isPrimitive($checkNumber)) {
                $this->found[] = $checkNumber;
            }
            $checkNumber += 2;
        }
        return $this->found;
    }

    private function isPrimitive(int $checkNumber): bool
    {
        for ($i = 2; $i < ceil($checkNumber / 2); $i++) {
            if ($checkNumber % $i == 0) {
                return false;
            }
        }
        return true;
    }
}
