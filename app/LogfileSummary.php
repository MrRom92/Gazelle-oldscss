<?php

namespace Gazelle;

class LogfileSummary {
    protected array $list;
    protected bool  $allChecksum = true;
    protected int   $lowestScore = 100;

    public function __construct(array $fileList = [], array $hashes = []) {
        $this->list = [];
        for ($n = 0, $end = count($fileList['error']); $n < $end; ++$n) {
            if ($fileList['error'][$n] === UPLOAD_ERR_OK) {
                $hash = hash_file(DIGEST_ALGO, $fileList['tmp_name'][$n]);
                if (in_array($hash, $hashes)) {
                    continue;
                }
                $hashes[] = $hash;
                $log = new Logfile($fileList['tmp_name'][$n], $fileList['name'][$n]);
                $this->allChecksum = $this->allChecksum && $log->checksum();
                $this->lowestScore = min($this->lowestScore, $log->score());
                $this->list[] = $log;
            }
        }
    }

    public function checksum(): bool {
        return $this->allChecksum;
    }

    public function checksumStatus(): string {
        return $this->allChecksum ? '1' : '0';
    }

    public function overallScore(): int {
        return $this->lowestScore;
    }

    /**
     * @return array<Logfile>
     */
    public function all(): array {
        return $this->list;
    }

    public function total(): int {
        return count($this->list);
    }
}
