<?php
namespace voilab\csv\test\stream;

final class GuesserTest extends \voilab\csv\test\Guesser
{
    use TraitResource;

    protected function setUp() : void
    {
        parent::setUp();
        switch ($this->getName()) {
            case 'testLineEndingN':
                $r = $this->getResource('csv-guesser-n.csv');
                break;
            case 'testLineEndingRN':
                $r = $this->getResource('csv-guesser-rn.csv');
                break;
            default:
                throw new \Exception('wrong setup');
        }
        $this->resource = $r;
    }
}
