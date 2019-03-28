<?php

declare(strict_types=1);

namespace Giove\StreamWrapper\Tests\File;

use Giove\StreamWrapper\File\Exception\WrapperException;
use Giove\StreamWrapper\StreamRegistry;
use Giove\StreamWrapper\Tests\TempFileTrait;
use PHPUnit\Framework\TestCase;

class FileGetContentsTest extends TestCase
{
    use TempFileTrait;

    public function testDirectoryAccessDenied(): void
    {
        $this->expectException(WrapperException::class);

        StreamRegistry::register('wrapper', $this->tempDir);
        file_put_contents($this->tempDir.'/new-file', 'test-data');
        chmod($this->tempDir, 0444);

        try {
            file_get_contents('wrapper://new-file');
        } catch (WrapperException $e) {
            $this->assertInstanceOf(\ErrorException::class, $e->getPrevious());
            $this->assertRegExp('/failed to open stream/', $e->getPrevious()->getMessage());
            $this->assertRegExp('/Permission denied/', $e->getPrevious()->getMessage());
            throw $e;
        }
    }

    public function testFileAccessDenied(): void
    {
        $this->expectException(WrapperException::class);

        StreamRegistry::register('wrapper', $this->tempDir);
        file_put_contents($this->tempDir.'/new-file', 'test-data');
        chmod($this->tempDir.'/new-file', 0222);

        try {
            file_get_contents('wrapper://new-file');
        } catch (WrapperException $e) {
            $this->assertInstanceOf(\ErrorException::class, $e->getPrevious());
            $this->assertRegExp('/failed to open stream/', $e->getPrevious()->getMessage());
            $this->assertRegExp('/Permission denied/', $e->getPrevious()->getMessage());
            throw $e;
        }
    }

    public function testNotExisting(): void
    {
        $this->expectException(WrapperException::class);
        StreamRegistry::register('wrapper', $this->tempDir);

        try {
            file_get_contents('wrapper://new-file');
        } catch (WrapperException $e) {
            $this->assertInstanceOf(\ErrorException::class, $e->getPrevious());
            $this->assertRegExp('/failed to open stream/', $e->getPrevious()->getMessage());
            $this->assertRegExp('/No such file or directory/', $e->getPrevious()->getMessage());
            throw $e;
        }
    }

    public function testSuccess(): void
    {
        StreamRegistry::register('wrapper', $this->tempDir);
        file_put_contents($this->tempDir.'/new-file', 'test-data');

        $this->assertEquals('test-data', file_get_contents('wrapper://new-file'));
    }
}
