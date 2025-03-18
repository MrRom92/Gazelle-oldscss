<?php

declare(strict_types=1);

namespace Gazelle;

class Error400 extends Error {
    public static function errorCode(): int {
        return 400;
    }

    public static function errorLabel(): string {
        return 'Bad Request';
    }

    public static function errorDescription(): string {
        return 'A parameter in the page request is missing or has an incorrect value. If you copy-pasted the page, make sure it is correct.';
    }
}
