<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

final class StreamTest extends TestCase
{
    protected function setUp() : void
    {
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';',
            'length' => 1024,
            'columns' => [
                'A' => function (string $data) {
                    return (int) $data;
                },
                'B' => function (string $data) {
                    return $data;
                }
            ]
        ]);
        $this->content = file_get_contents(__DIR__ . '/fixtures/csv-parser.csv');
    }

    public function testPsrStream() : void
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type', 'text/csv'], $this->content)
        ]);
        $stack = HandlerStack::create($mock);
        $client = new Client(['handler' => $stack]);

        $response = $client->request('GET', '/');
        $result = $this->parser->fromStream($response->getBody());

        $expect = [
            [ 'A' => 4, 'B' => 'hello' ],
            [ 'A' => 9, 'B' => 'world' ]
        ];
        $this->assertEquals($result, $expect);
    }
}
