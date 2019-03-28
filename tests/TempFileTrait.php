<?php

declare(strict_types=1);

namespace Giove\StreamWrapper\Tests;

use Giove\StreamWrapper\StreamRegistry;

trait TempFileTrait
{
    /**
     * @var string
     */
    private $tempFile;

    /**
     * @var string
     */
    private $tempDir;

    private $registeredProtocols = [];

    protected function setUp()
    {
        $this->tempDir = sys_get_temp_dir() . '/php-wrapper';
        if (@mkdir($this->tempDir, 0777) === false) {
            throw new \Exception('Can\'t create temp folder');
        }
    }

    protected function tearDown()
    {
        foreach (StreamRegistry::getRegisteredList() as $protocol => $rootPath) {
            StreamRegistry::unregister($protocol);
        }

        chmod($this->tempDir, 0777);
        $this->removeDirectory($this->tempDir);
    }

    private function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir."/".$object)) {
                    $this->removeDirectory($dir."/".$object);
                } else {
                    unlink($dir."/".$object);
                }
            }
        }

        rmdir($dir);
    }
}
