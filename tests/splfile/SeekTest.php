<?php
namespace voilab\csv\test\splfile;

class SeekTest extends \voilab\csv\test\Seek
{
    use TraitResource;

    protected function setUp() : void
    {
        parent::setUp();
        $this->resource = $this->getResource('csv-seek.csv');
    }
}
