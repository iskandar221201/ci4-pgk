<?php

namespace App\Libraries;

class AppLogger
{
    /**
     * Build a JSON-encoded log payload string.
     *
     * If $e is provided, an 'exception' key is added to the payload.
     * If json_encode fails (e.g. non-serializable context), a fallback
     * JSON string is returned so logging never crashes the application.
     */
    private static function buildLogPayload(string $level, string $action, array $context = [], ?\Throwable $e = null): string
    {
        $payload = [
            'timestamp' => date('c'),
            'level'     => strtoupper($level),
            'action'    => $action,
            'user_id'   => function_exists('auth') && auth()->loggedIn() ? auth()->id() : null,
            'ip'        => service('request')->getIPAddress(),
            'context'   => $context,
        ];

        if ($e !== null) {
            $payload['exception'] = [
                'class'   => get_class($e),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ];
        }

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            return json_encode([
                'timestamp' => date('c'),
                'level'     => strtoupper($level),
                'action'    => $action,
                'error'     => 'context_not_serializable',
            ]);
        }

        return $json;
    }

    /**
     * Log an informational message.
     *
     * @warning Do NOT pass sensitive data (PII, tokens, passwords) in $context.
     */
    public static function info(string $action, array $context = []): void
    {
        log_message('info', static::buildLogPayload('INFO', $action, $context));
    }

    /**
     * Log a warning message.
     *
     * @warning Do NOT pass sensitive data (PII, tokens, passwords) in $context.
     */
    public static function warning(string $action, array $context = []): void
    {
        log_message('warning', static::buildLogPayload('WARNING', $action, $context));
    }

    /**
     * Log an error message. Pass $e to include exception details in the payload.
     *
     * @warning Do NOT pass sensitive data (PII, tokens, passwords) in $context.
     */
    public static function error(string $action, array $context = [], ?\Throwable $e = null): void
    {
        log_message('error', static::buildLogPayload('ERROR', $action, $context, $e));
    }
}
