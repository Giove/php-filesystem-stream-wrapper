<?php

declare(strict_types=1);

namespace Giove\StreamWrapper\Tests\File;

use Giove\StreamWrapper\File\Exception\WrapperException;
use Giove\StreamWrapper\StreamRegistry;
use Giove\StreamWrapper\Tests\TempFileTrait;
use PHPUnit\Framework\TestCase;

class ResourceTest extends TestCase
{
    use TempFileTrait;

    public function testDoubleClose(): void
    {
        $this->expectException(WrapperException::class);
        StreamRegistry::register('wrapper', $this->tempDir);

        touch('wrapper://test-file');
        $handle = fopen('wrapper://test-file', 'w');

        $fsHandle = StreamRegistry::getLastHandle('wrapper://test-file');
        fclose($fsHandle);

        fclose($handle);
    }
}
