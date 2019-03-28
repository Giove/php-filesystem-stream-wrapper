<?php

declare(strict_types=1);

namespace Giove\StreamWrapper\Tests\File;

use Giove\StreamWrapper\File\Exception\WrapperException;
use Giove\StreamWrapper\StreamRegistry;
use Giove\StreamWrapper\Tests\TempFileTrait;
use PHPUnit\Framework\TestCase;

class PhpSettingsTest extends TestCase
{
    use TempFileTrait;

    public function testErrorReporting(): void
    {
        $this->expectException(WrapperException::class);
        StreamRegistry::register('wrapper', $this->tempDir);
        $originalLevel = error_reporting();

        try {
            error_reporting(E_ERROR);
            rmdir('wrapper://non-existing'); //normally generates warning
        } finally {
            error_reporting($originalLevel);
        }
    }
}
