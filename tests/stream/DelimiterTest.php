<?php
namespace voilab\csv\test\stream;

final class DelimiterTest extends \voilab\csv\test\Delimiter
{
    use TraitResource;

    protected function setUp() : void
    {
        parent::setUp();
        switch ($this->getName()) {
            case 'testSemiColon':
                $r = $this->getResource('csv-delimiter.csv');
                break;
            case 'testComma':
                $r = $this->getResource('csv-delimiter-comma.csv');
                break;
            case 'testTab':
                $r = $this->getResource('csv-delimiter-tab.csv');
                break;
            default:
                throw new \Exception('wrong setup');
        }
        $this->resource = $r;
    }
}
