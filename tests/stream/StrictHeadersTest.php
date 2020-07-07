<?php
namespace voilab\csv\test\stream;

class StrictHeadersTest extends \voilab\csv\test\StrictHeaders
{
    use TraitResource;

    protected function setUp() : void
    {
        parent::setUp();
        $this->resource = $this->getResource('csv-strict-headers.csv');
    }
}
