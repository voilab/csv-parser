<?php
namespace voilab\csv\test\splfile;

final class AutotrimTest extends \voilab\csv\test\Autotrim
{
    use TraitResource;

    protected function setUp() : void
    {
        parent::setUp();
        $this->resource = $this->getResource('csv-autotrim.csv');
    }
}
