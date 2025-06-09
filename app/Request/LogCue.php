<?php

namespace Gazelle\Request;

class LogCue {
    public function __construct(
        public readonly bool $needLogChecksum = true,
        public readonly bool $needCue         = true,
        public readonly bool $needLog         = true,
        public readonly int  $minScore        = 100,
    ) {}

    public function isValid(): bool {
        return $this->minScore >= 0 and $this->minScore <= 100;
    }

    public function dbValue(): string {
        $value = [];
        if ($this->needLog) {
            $value[] = 'Log';
            if ($this->isValid() && $this->minScore > 0) {
                $value[] = ($this->minScore == 100) ? '(100%)' : "(>= {$this->minScore}%)";
            }
        }
        if ($this->needCue) {
            $value[] = (count($value)) ? '+ Cue' : 'Cue';
        }
        return implode(' ', $value);
    }
}
