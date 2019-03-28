<?php

declare(strict_types=1);

namespace Giove\StreamWrapper\File;

use Giove\StreamWrapper\Exception\InvalidResourceException;
use Giove\StreamWrapper\File\Exception\WrapperException;
use Giove\StreamWrapper\StreamRegistry;

class FileStreamWrapper
{
    /**
     * @var resource
     */
    private $handle;

    /**
     * @var resource
     */
    private $dirHandle;

    /**
     * @var string
     */
    private $path;

    /**
     * http://php.net/manual/en/streamwrapper.stream-open.php
     */
    public $context;

    /**
     * @throws WrapperException
     */
    public function stream_open(string $fullPath, string $mode, int $options, string &$openedPath = null): bool
    {
        $resolved = StreamRegistry::resolve($fullPath);
        $this->path = $fullPath;

        return $this->run(function () use ($resolved, $mode) {
            $this->handle = fopen($resolved, $mode);
            StreamRegistry::put($this);
            return true;
        });
    }

    /**
     * @return resource
     */
    public function getHandle()
    {
        return $this->handle;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @throws WrapperException
     */
    public function stream_close(): void
    {
        $this->run(function () {
            fclose($this->handle);
        });
        StreamRegistry::remove($this);
    }

    public function stream_flush(): bool
    {
        return $this->run(function () {
            return fflush($this->handle);
        });
    }

    public function stream_read(int $count): string
    {
        return $this->run(function () use ($count) {
            return fread($this->handle, $count);
        });
    }

    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        return $this->run(function () use ($offset, $whence) {
            return 0 === fseek($this->handle, $offset, $whence);
        });
    }

    public function stream_stat(): array
    {
        return $this->run(function () {
            return fstat($this->handle);
        });
    }

    public function stream_tell(): int
    {
        return $this->run(function () {
            return ftell($this->handle);
        });
    }

    public function stream_truncate(int $newSize)
    {
        return $this->run(function () use ($newSize) {
            return ftruncate($this->handle, $newSize);
        });
    }

    public function stream_write($data)
    {
        return $this->run(function () use ($data) {
            return fwrite($this->handle, $data);
        });
    }

    public function stream_eof(): bool
    {
        return $this->run(function () {
            return feof($this->handle);
        });
    }

    /**
     * @return resource
     */
    public function stream_cast(int $castAs)
    {
        return $this->handle;
    }

    /**
     * @throws WrapperException
     */
    public function dir_opendir(string $fullPath, int $options): bool
    {
        $resolved = StreamRegistry::resolve($fullPath);
        $this->path = $fullPath;

        return $this->run(function () use ($resolved) {
            $this->handle = opendir($resolved, $this->context);
            StreamRegistry::put($this);
            return true;
        });
    }

    /**
     * @throws WrapperException
     */
    public function dir_closedir()
    {
        return $this->run(function () {
            return closedir($this->handle);
        });
    }

    /**
     * @return string|bool
     * @throws WrapperException
     */
    public function dir_readdir()
    {
        return $this->run(function () {
            return readdir($this->handle);
        });
    }

    public function dir_rewinddir(): ?bool
    {
        return $this->run(function () {
            return rewinddir($this->handle);
        });
    }

    /**
     * @throws WrapperException
     */
    public function mkdir(string $path, int $mode, int $options): bool
    {
        $resolved = StreamRegistry::resolve($path);

        return $this->run(function () use ($resolved, $mode, $options) {
            return mkdir($resolved, $mode, (bool) ($options & STREAM_MKDIR_RECURSIVE), $this->context);
        });
    }

    /**
     * @throws WrapperException
     */
    public function rename(string $oldname, string $newname): bool
    {
        $oldname = StreamRegistry::resolve($oldname);
        $newname = StreamRegistry::resolve($newname);

        return $this->run(function () use ($oldname, $newname) {
            return rename($oldname, $newname, $this->context);
        });
    }

    /**
     * @throws WrapperException
     */
    public function rmdir(string $path, int $options): bool
    {
        $resolved = StreamRegistry::resolve($path);

        return $this->run(function () use ($resolved, $options) {
            if ($options & STREAM_MKDIR_RECURSIVE) {
                while (file_exists($resolved)) {
                    rmdir($resolved, $this->context);
                    $resolved = dirname($resolved);
                }
            }
            return rmdir($resolved, $this->context);
        });
    }

    /**
     * @throws WrapperException
     */
    public function stream_lock(int $operation): bool
    {
        return $this->run(function () use ($operation) {
            return flock($this->handle, $operation);
        });
    }

    public function stream_metadata(string $path, int $option, $value): bool
    {
        $resolved = StreamRegistry::resolve($path);

        return $this->run(function () use ($option, $resolved, $value) {
            switch ($option) {
                case STREAM_META_TOUCH:
                    $now = time();
                    $time = array_key_exists(0, $value) ? $value[0] : $now;
                    $atime = array_key_exists(1, $value) ? $value[1] : $now;
                    return touch($resolved, $time, $atime);
                case STREAM_META_OWNER_NAME:
                case STREAM_META_OWNER:
                    return chown($resolved, $value);
                case STREAM_META_GROUP_NAME:
                case STREAM_META_GROUP:
                    return chgrp($resolved, $value);
                case STREAM_META_ACCESS:
                    return chmod($resolved, $value);
                default:
                    return false;
            }
        });
    }

    public function stream_set_option(int $option, int $arg1, int $arg2): bool
    {
        return false; //not implemented
    }

    /**
     * @throws WrapperException
     */
    public function unlink(string $path): bool
    {
        $resolved = StreamRegistry::resolve($path);

        return $this->run(function () use ($resolved) {
            return unlink($resolved);
        });
    }

    /**
     * @return array|bool
     * @throws WrapperException
     */
    public function url_stat(string $path, int $flags)
    {
        $resolved = StreamRegistry::resolve($path);
        $link = (bool) ($flags & STREAM_URL_STAT_LINK);
        $quiet = (bool) ($flags & STREAM_URL_STAT_QUIET);

        try {
            return $this->run(function () use ($resolved, $link) {
                return $link ? lstat($resolved) : stat($resolved);
            });
        } catch (WrapperException $e) {
            if ($quiet) {
                return false;
            }
            throw $e;
        }
    }

    /**
     * @param resource $sourceHandle
     * @throws WrapperException
     */
    public function streamCopyFrom($sourceHandle, int $maxLength = -1, int $offset = 0): int
    {
        if (! is_resource($sourceHandle)) {
            throw new InvalidResourceException();
        }

        return $this->run(function () use ($sourceHandle, $maxLength, $offset) {
            return stream_copy_to_stream($sourceHandle, $this->handle, $maxLength, $offset);
        });
    }

    /**
     * @throws WrapperException
     */
    private function run(callable $function)
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        try {
            return $function();
        } catch (\ErrorException $e) {
            throw new WrapperException('Operation error: '. $e->getMessage(), 0, $e);
        } finally {
            restore_error_handler();
        }
    }
}
