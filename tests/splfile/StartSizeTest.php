<?php
namespace voilab\csv\test\splfile;

class StartSizeTest extends \voilab\csv\test\StartSize
{
    use TraitResource;

    protected function setUp() : void
    {
        parent::setUp();
        $this->resource = $this->getResource('csv-start-size.csv');
    }
}
