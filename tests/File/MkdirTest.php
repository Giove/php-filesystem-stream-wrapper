<?php

declare(strict_types=1);

namespace Giove\StreamWrapper\Tests\File;

use Giove\StreamWrapper\File\Exception\WrapperException;
use Giove\StreamWrapper\StreamRegistry;
use Giove\StreamWrapper\Tests\TempFileTrait;
use PHPUnit\Framework\TestCase;

class MkdirTest extends TestCase
{
    use TempFileTrait;

    public function testDirectoryPermissionDenied(): void
    {
        $this->expectException(WrapperException::class);
        StreamRegistry::register('wrapper', $this->tempDir);
        chmod($this->tempDir, 0555);

        try {
            mkdir('wrapper://new-dir');
        } catch (WrapperException $e) {
            $this->assertInstanceOf(\ErrorException::class, $e->getPrevious());
            $this->assertRegExp('/Permission denied/', $e->getPrevious()->getMessage());
            throw $e;
        }
    }

    public function testSuccess(): void
    {
        StreamRegistry::register('wrapper', $this->tempDir);

        mkdir('wrapper://new-dir');
        $this->assertDirectoryExists($this->tempDir.'/new-dir');
    }

    public function testSuccessRecursive(): void
    {
        StreamRegistry::register('wrapper', $this->tempDir);

        mkdir('wrapper://new-dir/lvl2', 0777, true);
        $this->assertDirectoryExists($this->tempDir.'/new-dir/lvl2');
    }
}
