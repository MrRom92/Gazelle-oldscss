<?php

declare(strict_types=1);

namespace Gazelle;

class Error429 extends Error {
    public static function errorCode(): int {
        return 429;
    }

    public static function errorLabel(): string {
        return 'Too Many Requests';
    }

    public static function errorDescription(): string {
        return 'You tried to do something too frequently. Slow down and try again later.';
    }
}
