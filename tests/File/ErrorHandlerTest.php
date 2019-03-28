<?php

declare(strict_types=1);

namespace Giove\StreamWrapper\Tests\File;

use Giove\StreamWrapper\File\Exception\WrapperException;
use Giove\StreamWrapper\StreamRegistry;
use Giove\StreamWrapper\Tests\TempFileTrait;
use PHPUnit\Framework\TestCase;

class ErrorHandlerTest extends TestCase
{
    use TempFileTrait;

    public function testRestoreErrorHandler()
    {
        //this handler should be fired ad the end
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
        $this->expectException(\ErrorException::class);

        StreamRegistry::register('wrapper', $this->tempDir);
        chmod($this->tempDir, 0555);

        try {
            touch('wrapper://new-file');
            $this->fail('expected exception');
        } catch (WrapperException $e) {
            //expected
        }

        $test['non-existing-key']; //should trigger error handler
    }
}
