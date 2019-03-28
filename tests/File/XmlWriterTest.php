<?php

declare(strict_types=1);

namespace Giove\StreamWrapper\Tests\File;

use Giove\StreamWrapper\StreamRegistry;
use Giove\StreamWrapper\Tests\TempFileTrait;
use PHPUnit\Framework\TestCase;

class XmlWriterTest extends TestCase
{
    use TempFileTrait;

    public function testWriteInMiddle(): void
    {
        StreamRegistry::register('wrapper', $this->tempDir);

        $writer = new \XMLWriter();
        $writer->openUri('wrapper://new-file');
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('lorem');
        $writer->writeRaw('ipsum');

        //write to handle in the middle of xml
        $writer->flush();
        fwrite(StreamRegistry::getLastHandle('wrapper://new-file'), '-dolor');

        $writer->endElement();
        $writer->endDocument();
        $writer->flush();
        unset($writer);

        $expected = '<?xml version="1.0" encoding="UTF-8"?>
<lorem>ipsum-dolor</lorem>
';
        $this->assertEquals($expected, file_get_contents($this->tempDir.'/new-file'));
    }

    public function testStreamCopyFile(): void
    {
        StreamRegistry::register('wrapper', $this->tempDir);
        file_put_contents($this->tempDir.'/stream-from', '-streamed-content');
        $handle = fopen($this->tempDir.'/stream-from', 'r');

        $writer = new \XMLWriter();
        $writer->openUri('wrapper://new-file');
        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('lorem');
        $writer->writeRaw('ipsum');

        //write to handle in the middle of xml
        $writer->flush();
        StreamRegistry::getLastWrapper('wrapper://new-file')->streamCopyFrom($handle);

        $writer->endElement();
        $writer->endDocument();
        $writer->flush();
        unset($writer);

        $expected = '<?xml version="1.0" encoding="UTF-8"?>
<lorem>ipsum-streamed-content</lorem>
';
        $this->assertEquals($expected, file_get_contents($this->tempDir.'/new-file'));
    }
}
