<?php
namespace voilab\csv\test\stream;

class MissingHeadersTest extends \voilab\csv\test\MissingHeaders
{
    use TraitResource;

    protected function setUp() : void
    {
        parent::setUp();
        $this->resource = $this->getResource('csv-missing-headers.csv');
    }
}
