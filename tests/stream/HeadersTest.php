<?php
namespace voilab\csv\test\stream;

class HeadersTest extends \voilab\csv\test\Headers
{
    use TraitResource;

    protected function setUp() : void
    {
        parent::setUp();
        switch ($this->getName()) {
            case 'testCarriageReturnHeaders':
                $r = $this->getResource('csv-carriagereturn-headers.csv');
                break;
            default:
                $r = $this->getResource('csv-no-header.csv');
        }
        $this->resource = $r;
    }

    public function testCarriageReturnHeaders() : void
    {
        $this->markTestIncomplete('Known issue. Headers with carriage returns in stream are not supported.');
    }
}
