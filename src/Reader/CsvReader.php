<?php

declare(strict_types=1);

namespace Reader;

class CsvReader
{
    public function read($pathToFile)
    {
        //open the file
        $file = fopen($pathToFile, 'r');

        $data_array = [];

        while (($data = fgetcsv($file, 1000, ',')) !== false) {
            $data_array[] = $data;
        }

        return $data_array;
    }
}
