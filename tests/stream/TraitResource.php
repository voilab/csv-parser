<?php
namespace voilab\csv\test\stream;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

trait TraitResource
{
    protected function getResource($file, $debug = false)
    {
        $this->parser->setOption('length', 1024);
        $file = $this->dir . '/' . $file;
        $mock = new MockHandler([
            new Response(200, ['Content-Type', 'text/csv'], file_get_contents($file))
        ]);
        $stack = HandlerStack::create($mock);
        $client = new Client(['handler' => $stack]);

        $response = $client->request('GET', '/');
        return new \voilab\csv\CsvStream($response->getBody(), [ 'debug' => $debug ]);
    }
}
