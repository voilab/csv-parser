<?php
namespace voilab\csv\test\splfile;

trait TraitResource
{
    protected function getResource($file, $debug = false)
    {
        $file = $this->dir . '/' . $file;
        $this->parser->setOption('debug', $debug);
        return new \voilab\csv\CsvSplFile(new \SplFileObject($file), [ 'debug' => $debug ]);
    }
}
