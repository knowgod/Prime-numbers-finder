<?php

namespace Primitive\SaveTo;

class File
{
    public function __construct(
        private readonly string $path,
        private readonly string $delimiter = ', ',
    ) {
    }

    /**
     * Read file values, clean up and rewrite the file.
     *
     * @return array
     */
    public function read(): array
    {
        $line         = trim(file_get_contents($this->path));
        $parsedValues = explode($this->delimiter, $line);
        $values       = array_map(function ($value) {
            return intval(trim($value, ", \n\r\t\v\0"));
        }, $parsedValues);

        $cleanValues = array_unique($values);
        file_put_contents($this->path, implode($this->delimiter, $cleanValues));

        return $cleanValues;
    }

    public function readLast(): ?int
    {
        $allValues = $this->read();
        $lastItem  = max($allValues);
        return !empty($lastItem) ? $lastItem : null;
    }

    /**
     * Appends data to the specified file.
     *
     * @param array $data The array of data to append to the file. Each element of the array will be joined using the specified delimiter.
     *
     * @return void
     * @throws \RuntimeException If the file cannot be opened or the data cannot be written.
     */
    public function append(array $data): void
    {
        try {
            $fp = fopen($this->path, 'a');
            if ($fp === false) {
                throw new \RuntimeException('Unable to open file');
            }
            $line = $this->delimiter . implode($this->delimiter, $data);
            fwrite($fp, $line);
            fclose($fp);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Unable to append to file', 0, $e);
        }
    }
}
