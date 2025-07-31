<?php

namespace Knowgod\Prime\SaveTo;

class File implements \Knowgod\Prime\Api\IoInterface
{
    private string $fullPath;

    public function __construct(
        private readonly string $path,
        private readonly string $delimiter = ', ',
    ) {
    }

    /**
     *
     * @return string
     */
    private function getFilePath(): string
    {
        if (empty($this->fullPath)) {
            $this->initFullPath($this->path);
        }
        return $this->fullPath;
    }

    /**
     * @param string|null $path
     *
     * @return void
     */
    private function initFullPath(?string $path): void
    {
        if (empty($path)) {
            throw new \InvalidArgumentException('Path cannot be empty');
        }
        $srcDir      = dirname(__FILE__);
        $projectRoot = realpath($srcDir . '/../../');
        $path        = $projectRoot . '/' . $path;
        $directory   = dirname($path);
        if (!file_exists($directory)) {
            mkdir($directory, 0775, true);
        }
        if (!file_exists($path)) {
            touch($path);
        }
        $this->fullPath = $path;
    }

    /**
     * Read file values, clean up and rewrite the file.
     *
     * @return array
     */
    public function read(): array
    {
        $line = trim(file_get_contents($this->getFilePath()));
        if (empty($line)) {
            $cleanValues = [];
        } else {
            $parsedValues = explode($this->delimiter, $line);
            $cleanValues = [];
            foreach ($parsedValues as $value) {
                if (empty($value)) {
                    continue;
                }
                $value               = intval(trim($value, ", \n\r\t\v\0"));
                $cleanValues[$value] = $value;
            }

            file_put_contents($this->getFilePath(), implode($this->delimiter, $cleanValues));
        }

        return $cleanValues;
    }

    public function readLast(): ?int
    {
        if (empty($allValues = $this->read())) {
            return null;
        }
        $lastItem = max($allValues);
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
            $fp = fopen($this->getFilePath(), 'a');
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
