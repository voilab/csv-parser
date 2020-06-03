<?php
namespace voilab\csv\test\iterable;

class HeadersTest extends \voilab\csv\test\Headers
{
    use TraitResource;

    protected function setUp() : void
    {
        parent::setUp();
        switch ($this->getName()) {
            case 'testCarriageReturnHeaders':
                $r = $this->getResource('csv-carriagereturn-headers.php');
                break;
            default:
                $r = $this->getResource('csv-no-header.csv');
        }
        $this->resource = $r;
    }
}
