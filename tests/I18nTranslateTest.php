<?php

use PHPUnit\Framework\TestCase;

final class I18nTranslateTest extends TestCase
{
    protected function setUp() : void
    {
        $i18n = new \voilab\csv\I18n([
            'NOFILE' => "Le fichier [%s] n'existe pas"
        ]);
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';'
        ], $i18n);
        $this->dir =  __DIR__ . '/fixtures/';
    }

    public function testNoFile() : void
    {
        $this->expectExceptionMessage("Le fichier [berthe.csv] n'existe pas");
        $result = $this->parser->fromFile('berthe.csv', [
            'columns' => [
                'A' => function (string $data) { return $data; }
            ]
        ]);
    }
}
