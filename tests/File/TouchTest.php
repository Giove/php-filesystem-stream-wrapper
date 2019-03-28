<?php

declare(strict_types=1);

namespace Giove\StreamWrapper\Tests\File;

use Giove\StreamWrapper\File\Exception\WrapperException;
use Giove\StreamWrapper\StreamRegistry;
use Giove\StreamWrapper\Tests\TempFileTrait;
use PHPUnit\Framework\TestCase;

class TouchTest extends TestCase
{
    use TempFileTrait;

    public function testDirectoryPermissionDenied(): void
    {
        $this->expectException(WrapperException::class);
        StreamRegistry::register('wrapper', $this->tempDir);
        chmod($this->tempDir, 0555);

        try {
            touch('wrapper://new-file');
        } catch (WrapperException $e) {
            $this->assertInstanceOf(\ErrorException::class, $e->getPrevious());
            $this->assertRegExp('/Unable to create file/', $e->getPrevious()->getMessage());
            $this->assertRegExp('/Permission denied/', $e->getPrevious()->getMessage());
            throw $e;
        }
    }

    public function testSuccess(): void
    {
        StreamRegistry::register('wrapper', $this->tempDir);

        touch('wrapper://new-file');
        $this->assertFileExists($this->tempDir.'/new-file');
    }
}
