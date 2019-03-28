<?php

declare(strict_types=1);

namespace Giove\StreamWrapper\Tests\File;

use Giove\StreamWrapper\StreamRegistry;
use Giove\StreamWrapper\Tests\TempFileTrait;
use PHPUnit\Framework\TestCase;

class DirTest extends TestCase
{
    use TempFileTrait;

    public function testDir(): void
    {
        StreamRegistry::register('wrapper', $this->tempDir);
        mkdir('wrapper://test-dir');
        touch('wrapper://test-dir/test-file');

        $this->assertDirectoryExists('wrapper://test-dir');

        $handle = opendir('wrapper://test-dir');
        $this->assertEquals('dir', filetype('wrapper://test-dir'));

        while (($file = readdir($handle)) !== false) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }
            $this->assertEquals('test-file', $file);
            $this->assertEquals('file', filetype('wrapper://test-dir/test-file'));
        }

        closedir($handle);
    }
}
