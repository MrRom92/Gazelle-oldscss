<?php

declare(strict_types=1);

namespace Gazelle;

class Error404 extends Error {
    public static function errorCode(): int {
        return 404;
    }

    public static function errorLabel(): string {
        return 'Not Found';
    }

    public static function errorDescription(): string {
        return 'The requested page does not exist, or if it did once, it no longer exists.';
    }
}
