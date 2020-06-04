<?php
namespace voilab\csv\test\iterable;

trait TraitResource
{
    protected function getResource($file, $delimiter = ';')
    {
        if (strpos($file, '.php') === false) {
            $file = $this->dir . '/' . $file;
            $str = trim(file_get_contents($file));
            $rows = $str ? explode("\n", $str) : [];

            $data = [];
            foreach ($rows as $row) {
                $data[] = explode($delimiter, $row);
            }
        } else {
            $data = require $this->dir . '/' . $file;
        }
        return new \voilab\csv\CsvIterable($data);
    }
}
