<?php

declare(strict_types=1);

namespace Giove\StreamWrapper;

use Giove\StreamWrapper\Exception\AlreadyRegisteredException;
use Giove\StreamWrapper\Exception\InvalidDirException;
use Giove\StreamWrapper\Exception\InvalidUrlException;
use Giove\StreamWrapper\Exception\NotRegisteredException;
use Giove\StreamWrapper\Exception\ProtocolRegisteredElsewhereException;
use Giove\StreamWrapper\Exception\ProtocolUnregisterException;
use Giove\StreamWrapper\Exception\RegisterFailedException;
use Giove\StreamWrapper\File\Exception\WrapperException;
use Giove\StreamWrapper\File\FileStreamWrapper;

class StreamRegistry
{
    private static $rootPaths = [];

    private static $wrapperMap = [];

    /**
     * @throws WrapperException
     */
    public static function register(string $protocol, string $rootPath): void
    {
        if (! is_dir($rootPath)) {
            throw new InvalidDirException(sprintf('"%s" is not a valid directory', $rootPath));
        }

        if (isset(self::$rootPaths[$protocol])) {
            throw new AlreadyRegisteredException(sprintf('Protocol "%s" is already registered', $protocol));
        }

        if (in_array($protocol, stream_get_wrappers())) {
            throw new ProtocolRegisteredElsewhereException(sprintf(
                'Protocol "%s" is already registered elsewhere',
                $protocol
            ));
        }

        $registered = @stream_wrapper_register($protocol, FileStreamWrapper::class);
        if (!$registered) {
            throw new RegisterFailedException(
                sprintf(
                    'Can\'t register protocol "%s" through stream_wrapper_register()',
                    $protocol
                )
            );
        }

        self::$rootPaths[$protocol] = realpath($rootPath);
    }

    /**
     * @throws WrapperException
     */
    public static function unregister(string $protocol): void
    {
        if (!isset(self::$rootPaths[$protocol])) {
            throw new NotRegisteredException();
        }

        if (in_array($protocol, stream_get_wrappers())) {
            $unregistered = @stream_wrapper_unregister($protocol);
            if (!$unregistered) {
                throw new ProtocolUnregisterException(sprintf(
                    'Can\'t unregister protocol "%s";',
                    $protocol
                ));
            }
        }

        unset(self::$rootPaths[$protocol]);
    }

    public static function getRegisteredList(): array
    {
        return self::$rootPaths;
    }

    /**
     * @throws WrapperException
     */
    public static function getRootPath(string $protocol): string
    {
        if (! isset(self::$rootPaths[$protocol])) {
            throw new WrapperException();
        }

        return self::$rootPaths[$protocol];
    }

    /**
     * @throws WrapperException
     */
    public static function resolve(string $filePath): string
    {
        list($protocol, $path) = self::parse($filePath);
        $rootPath = self::getRootPath($protocol);

        return sprintf('%s/%s', $rootPath, $path);
    }

    /**
     * @return resource
     * @throws WrapperException
     */
    public static function getLastHandle(string $filePath)
    {
        return self::getLastWrapper($filePath)->getHandle();
    }

    /**
     * @throws WrapperException
     */
    public static function getLastWrapper(string $filePath): FileStreamWrapper
    {
        if (! isset(self::$wrapperMap[$filePath])) {
            throw new WrapperException();
        }

        return end(self::$wrapperMap[$filePath]);
    }

    /**
     * @return FileStreamWrapper[]
     * @throws WrapperException
     */
    public static function getWrappers(string $filePath): array
    {
        if (! isset(self::$wrapperMap[$filePath])) {
            throw new WrapperException();
        }

        return self::$wrapperMap[$filePath];
    }

    /**
     * @internal
     */
    public static function put(FileStreamWrapper $wrapper): void
    {
        self::$wrapperMap[$wrapper->getPath()][] = $wrapper;
    }

    /**
     * @internal
     */
    public static function remove(FileStreamWrapper $wrapper): void
    {
        $index = array_search($wrapper, self::$wrapperMap[$wrapper->getPath()]);
        unset(self::$wrapperMap[$wrapper->getPath()][$index]);
    }

    /**
     * @throws WrapperException
     */
    private static function parse(string $url): array
    {
        $parsed = parse_url($url);
        if (! $parsed || empty($parsed['scheme']) || empty($parsed['host'])) {
            throw new InvalidUrlException();
        }

        return [strtolower($parsed['scheme']), $parsed['host'].($parsed['path'] ?? '')];
    }
}
