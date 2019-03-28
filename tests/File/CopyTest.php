<?php

declare(strict_types=1);

namespace Giove\StreamWrapper\Tests\File;

use Giove\StreamWrapper\StreamRegistry;
use Giove\StreamWrapper\Tests\TempFileTrait;
use PHPUnit\Framework\TestCase;

class CopyTest extends TestCase
{
    use TempFileTrait;

    public function testCopyFromDirectFilesystem(): void
    {
        StreamRegistry::register('wrapper', $this->tempDir);
        copy(__FILE__, 'wrapper://test1');

        $this->assertFileEquals(__FILE__, 'wrapper://test1');
    }

    public function testCopyFromWrapper(): void
    {
        StreamRegistry::register('wrapper', $this->tempDir);
        copy(__FILE__, 'wrapper://test1');
        copy('wrapper://test1', $this->tempDir.'/test2');

        $this->assertFileEquals(__FILE__, $this->tempDir.'/test2');
    }
}
