<?php

declare(strict_types=1);

namespace Giove\StreamWrapper\Tests\File;

use Giove\StreamWrapper\File\Exception\WrapperException;
use Giove\StreamWrapper\StreamRegistry;
use Giove\StreamWrapper\Tests\TempFileTrait;
use PHPUnit\Framework\TestCase;

class RmdirTest extends TestCase
{
    use TempFileTrait;

    public function testDirectoryPermissionDenied(): void
    {
        $this->expectException(WrapperException::class);
        StreamRegistry::register('wrapper', $this->tempDir);

        try {
            mkdir('wrapper://new-dir');
            chmod($this->tempDir, 0555);
            rmdir('wrapper://new-dir');
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
        rmdir('wrapper://new-dir');
        $this->assertDirectoryNotExists($this->tempDir.'/new-dir');
    }
}
