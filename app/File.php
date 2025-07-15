<?php

namespace Gazelle;

abstract class File extends BaseObject {
    /**
     * Path to stored file
     */
    abstract public function path(): string;

    /**
     * Does the file exist?
     */
    public function exists(): bool {
        return file_exists($this->path());
    }

    /**
     * Store some data as a file on disk at the specified path.
     */
    public function put(string $contents): bool {
        return file_put_contents($this->path(), $contents) !== false;
    }

    /**
     * Retrieve the contents of the stored file.
     */
    public function get(): string|false {
        return file_get_contents($this->path());
    }

    /**
     * Retrieve the size of the stored file. Note that the size
     * of a file that does not exist will be 0 (which may occur
     * if something bad happened). Use the exists() method to
     * distinguish between a true 0-byte file and missing file.
     */
    public function size(): int {
        return (int)filesize($this->path());
    }

    /**
     * Remove the stored file.
     */
    public function remove(): int {
        return (int)unlink($this->path());
    }
}
