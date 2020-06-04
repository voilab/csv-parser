<?php
namespace voilab\csv\test\file;

final class BadFormatTest extends \voilab\csv\test\BadFormat
{
    use TraitResource;

    protected function setUp() : void
    {
        parent::setUp();
        switch ($this->getName()) {
            case 'testDoubleHeaders':
                $r = $this->getResource('csv-badformat-double-header.csv');
                break;
            case 'testEmpty':
                $r = $this->getResource('csv-badformat-empty.csv');
                break;
            case 'testBuggyStrict':
            case 'testBuggyLoose':
                $r = $this->getResource('csv-badformat-buggy.csv');
                break;
            default:
                throw new \Exception('wrong setup');
        }
        $this->resource = $r;
    }
}
