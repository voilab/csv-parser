<?php
namespace voilab\csv\test\stream;

final class EnclosureTest extends \voilab\csv\test\Enclosure
{
    use TraitResource;

    protected function setUp() : void
    {
        parent::setUp();
        $this->resource = $this->getResource('csv-enclosure.csv');
    }
}
