<?php

declare(strict_types=1);

namespace Giove\StreamWrapper\Tests\File;

use Giove\StreamWrapper\StreamRegistry;
use Giove\StreamWrapper\Tests\TempFileTrait;
use PHPUnit\Framework\TestCase;

class StatTest extends TestCase
{
    use TempFileTrait;

    public function testChmodAndFilePerms(): void
    {
        StreamRegistry::register('wrapper', $this->tempDir);
        touch('wrapper://new-file');
        chmod('wrapper://new-file', 0700);

        $permissions = fileperms('wrapper://new-file');
        $permissions = substr(sprintf('%o', $permissions), -4);
        $this->assertEquals($permissions, '0700');
    }

    public function testInode(): void
    {
        StreamRegistry::register('wrapper', $this->tempDir);
        touch('wrapper://new-file');
        $this->assertNotFalse(fileinode('wrapper://new-file'));
    }

    public function testFilesize(): void
    {
        StreamRegistry::register('wrapper', $this->tempDir);
        touch('wrapper://new-file');
        $this->assertNotFalse(filesize('wrapper://new-file'));
    }

    public function testFileownerAndFilegroup(): void
    {
        StreamRegistry::register('wrapper', $this->tempDir);
        touch('wrapper://new-file');
        $this->assertNotFalse(fileowner('wrapper://new-file'));
        $this->assertNotFalse(filegroup('wrapper://new-file'));
    }

    public function testFiletime(): void
    {
        StreamRegistry::register('wrapper', $this->tempDir);
        touch('wrapper://new-file');
        $this->assertNotFalse(fileatime('wrapper://new-file'));
        $this->assertNotFalse(filemtime('wrapper://new-file'));
        $this->assertNotFalse(filectime('wrapper://new-file'));
    }

    public function testFiletype(): void
    {
        StreamRegistry::register('wrapper', $this->tempDir);
        touch('wrapper://new-file');

        $this->assertEquals('file', filetype('wrapper://new-file'));
    }

    public function testIsFunctions(): void
    {
        StreamRegistry::register('wrapper', $this->tempDir);
        mkdir('wrapper://new-dir');
        touch('wrapper://new-dir/new-file');
        chmod('wrapper://new-dir/new-file', 0777);

        //wrappers isn't supported:
        symlink($this->tempDir.'/new-dir/new-file', $this->tempDir.'/new-dir/new-file2');

        $this->assertEquals(true, is_dir('wrapper://new-dir'));
        $this->assertEquals(true, is_file('wrapper://new-dir/new-file'));
        $this->assertEquals(true, is_link('wrapper://new-dir/new-file2'));

        $this->assertEquals(true, is_writable('wrapper://new-dir/new-file'));
        $this->assertEquals(true, is_readable('wrapper://new-dir/new-file'));
        $this->assertEquals(true, is_executable('wrapper://new-dir/new-file'));
    }

    public function testFileExists(): void
    {
        StreamRegistry::register('wrapper', $this->tempDir);
        touch('wrapper://new-file');

        $this->assertEquals(true, file_exists('wrapper://new-file'));
    }

    public function testStat(): void
    {
        StreamRegistry::register('wrapper', $this->tempDir);
        touch('wrapper://new-file');
        $stat = stat('wrapper://new-file');

        //wrappers isn't supported:
        symlink($this->tempDir.'/new-file', $this->tempDir.'/new-file2');
        $lstat = lstat('wrapper://new-file2');

        $this->assertIsArray($stat);
        $this->assertEquals(stat('wrapper://new-file'), $stat);

        $this->assertIsArray($lstat);
        $this->assertEquals(lstat('wrapper://new-file2'), $lstat);
    }
}
