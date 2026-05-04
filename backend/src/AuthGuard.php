<?php

declare(strict_types=1);

final class AuthGuard
{
    private static function ensureSessionStarted(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function requireApiAuth(): void
    {
        self::ensureSessionStarted();

        if (empty($_SESSION['auth_user'])) {
            ApiResponse::error('Unauthorized', 401);
            exit;
        }
    }
}
