<?php

namespace App\Exceptions\Files;

use RuntimeException;

/**
 * Thrown when a file cannot be accepted into the public upload directory.
 *
 * The uploader is a domain service, so it raises this instead of calling
 * abort(); the HTTP status is decided at the edge in bootstrap/app.php.
 */
final class UnsupportedUploadException extends RuntimeException
{
    public static function extension(string $extension): self
    {
        return new self($extension === ''
            ? 'This file type is not allowed.'
            : sprintf('Files of type "%s" are not allowed.', $extension));
    }

    public static function directory(string $directory): self
    {
        return new self(sprintf('Invalid upload directory "%s".', $directory));
    }
}
