<?php
namespace voilab\csv\test\stream;

class SeekTest extends \voilab\csv\test\Seek
{
    use TraitResource;

    protected function setUp() : void
    {
        parent::setUp();
        $this->resource = $this->getResource('csv-seek.csv', true);
    }

    public function testSeekWithStartUnsync() : void
    {
        $this->markTestIncomplete('Known issue. Stream and seek does not work together.');
    }

    public function testSeek() : void
    {
        $this->markTestIncomplete('Known issue. Stream and seek does not work together.');
    }

    public function testSeekPointerOverflow() : void
    {
        $result = $this->parser->parse($this->resource, [
            'start' => 0,
            'size' => 3
        ]);

        $this->expectException('RuntimeException');
        $result = $this->parser->parse($this->resource, [
            'size' => 3,
            'seek' => 500000
        ]);

        $expect = [];
        $this->assertEquals($result, $expect);
    }
}
