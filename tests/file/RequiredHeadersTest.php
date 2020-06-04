<?php
namespace voilab\csv\test\file;

class RequiredHeadersTest extends \voilab\csv\test\RequiredHeaders
{
    use TraitResource;

    protected function setUp() : void
    {
        parent::setUp();
        $this->resource = $this->getResource('csv-required-headers.csv');
    }
}
