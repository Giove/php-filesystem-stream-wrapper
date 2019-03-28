<?php

declare(strict_types=1);

namespace Giove\StreamWrapper\Tests\File;

use Giove\StreamWrapper\File\Exception\WrapperException;
use Giove\StreamWrapper\StreamRegistry;
use Giove\StreamWrapper\Tests\TempFileTrait;
use PHPUnit\Framework\TestCase;

class FilePutContentsTest extends TestCase
{
    use TempFileTrait;

    public function testDirectoryPermissionDenied(): void
    {
        $this->expectException(WrapperException::class);
        StreamRegistry::register('wrapper', $this->tempDir);

        chmod($this->tempDir, 0555);
        try {
            file_put_contents('wrapper://new-file', 'test-data');
        } catch (WrapperException $e) {
            $this->assertInstanceOf(\ErrorException::class, $e->getPrevious());
            $this->assertRegExp('/failed to open stream/', $e->getPrevious()->getMessage());
            $this->assertRegExp('/Permission denied/', $e->getPrevious()->getMessage());
            throw $e;
        }
    }

    public function testFilePermissionDenied(): void
    {
        $this->expectException(WrapperException::class);
        StreamRegistry::register('wrapper', $this->tempDir);

        touch($this->tempDir.'/new-file');
        chmod($this->tempDir.'/new-file', 0555);

        try {
            file_put_contents('wrapper://new-file', 'test-data');
        } catch (WrapperException $e) {
            $this->assertInstanceOf(\ErrorException::class, $e->getPrevious());
            $this->assertRegExp('/failed to open stream/', $e->getPrevious()->getMessage());
            $this->assertRegExp('/Permission denied/', $e->getPrevious()->getMessage());
            throw $e;
        }
    }

    public function testSuccess(): void
    {
        StreamRegistry::register('wrapper', $this->tempDir);

        file_put_contents('wrapper://new-file', 'test-data');
        $this->assertEquals('test-data', file_get_contents($this->tempDir.'/new-file'));
    }
}
