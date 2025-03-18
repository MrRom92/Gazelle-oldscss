<?php

declare(strict_types=1);

namespace Gazelle;

class Error500 extends Error {
    public static function errorCode(): int {
        return 500;
    }

    public static function errorLabel(): string {
        return 'Server Error';
    }

    public static function errorDescription(): string {
        return "This one's on us, it looks like you tripped over a bug in the code.";
    }
}
