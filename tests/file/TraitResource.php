<?php
namespace voilab\csv\test\file;

trait TraitResource
{
    protected function getResource($file)
    {
        $file = $this->dir . '/' . $file;
        return new \voilab\csv\CsvResource(fopen($file, 'r'));
    }
}
