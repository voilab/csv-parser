<?php
namespace voilab\csv\test\stream;

class OnBeforeColumnParseTest extends \voilab\csv\test\OnBeforeColumnParse
{
    use TraitResource;

    protected function setUp() : void
    {
        parent::setUp();
        $this->resource = $this->getResource('csv-on-before-column-parse.csv');
    }
}
