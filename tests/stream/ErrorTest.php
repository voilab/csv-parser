<?php
namespace voilab\csv\test\stream;

final class ErrorTest extends \voilab\csv\test\Error
{
    use TraitResource;

    protected function setUp() : void
    {
        parent::setUp();
        $this->resource = $this->getResource('csv-parser.csv');
    }
}
