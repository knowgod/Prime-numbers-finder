<?php

namespace Knowgod\Prime\Api;

interface IoInterface {
    /**
     * Read file values, clean up and rewrite the file.
     *
     * @return array
     */
    public function read(): array;

    /**
     * Appends data to the specified file.
     *
     * @param array $data The array of data to append to the file. Each element of the array will be joined using the specified delimiter.
     *
     * @return void
     * @throws \RuntimeException If the file cannot be opened or the data cannot be written.
     */
    public function append(array $data): void;

    public function readLast(): ?int;
}
