<?php

namespace Gazelle\File;

// see the comment in File\RipLog

class RipLogHTML extends \Gazelle\File {
    public function __construct(
        public readonly int $id,
        public readonly int|string $logId,
    ) {}

    public function flush(): static {
        return $this;
    }

    public function location(): string {
        return "";
    }

    public function link(): string {
        return "";
    }

    /**
     * Path of a HTML-ized rip log.
     */
    public function path(): string {
        $key = strrev(sprintf('%04d', $this->id));
        return sprintf("%s/%02d/%02d/{$this->id}_{$this->logId}.html",
            STORAGE_PATH_RIPLOGHTML, substr($key, 0, 2), substr($key, 2, 2)
        );
    }

    /**
     * Remove one or more HTML-ized rip logs of a torrent
     */
    public function remove(): int {
        if ($this->exists()) {
             return (int)unlink($this->path());
        } elseif ($this->logId === '*') {
            $fileList = glob($this->path());
            if ($fileList === false) {
                return 0;
            }
            foreach ($fileList as $path) {
                if (preg_match('/(\d+)\.html/', $path, $match)) {
                    if (!new self($this->id, (int)$match[1])->remove()) {
                        return 0;
                    }
                }
            }
            return 1;
        }
        return 0;
    }
}
