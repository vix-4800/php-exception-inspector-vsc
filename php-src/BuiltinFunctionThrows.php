<?php

declare(strict_types=1);

namespace Vix\ExceptionInspector;

use DateMalformedStringException;
use IntlException;
use JsonException;
use PDOException;
use Random\RandomException;
use SocketException;
use ValueError;

/**
 * Registry of built-in PHP functions that can throw exceptions
 */
final class BuiltinFunctionThrows
{
    /**
     * Map of function names to exceptions they can throw
     * Format: ['functionName' => ['ExceptionType1', 'ExceptionType2']]
     *
     * @var array<string, string[]>
     */
    private const array FUNCTION_THROWS_MAP = [
        // JSON functions
        'json_encode' => [JsonException::class],
        'json_decode' => [JsonException::class],

        // Date/Time functions
        'date_create' => [DateMalformedStringException::class],
        'date_create_immutable' => [DateMalformedStringException::class],
        'date_create_from_format' => [DateMalformedStringException::class],
        'date_create_immutable_from_format' => [DateMalformedStringException::class],
        'date_parse' => [DateMalformedStringException::class],
        'date_parse_from_format' => [DateMalformedStringException::class],

        // String functions with ValueError
        'mb_check_encoding' => [ValueError::class],
        'mb_chr' => [ValueError::class],
        'mb_convert_case' => [ValueError::class],
        'mb_convert_encoding' => [ValueError::class],
        'mb_convert_kana' => [ValueError::class],
        'mb_decode_numericentity' => [ValueError::class],
        'mb_encode_numericentity' => [ValueError::class],
        'mb_ord' => [ValueError::class],
        'mb_scrub' => [ValueError::class],
        'mb_strcut' => [ValueError::class],
        'mb_strimwidth' => [ValueError::class],
        'mb_stripos' => [ValueError::class],
        'mb_stristr' => [ValueError::class],
        'mb_strlen' => [ValueError::class],
        'mb_strpos' => [ValueError::class],
        'mb_strrchr' => [ValueError::class],
        'mb_strrichr' => [ValueError::class],
        'mb_strripos' => [ValueError::class],
        'mb_strrpos' => [ValueError::class],
        'mb_strstr' => [ValueError::class],
        'mb_strtolower' => [ValueError::class],
        'mb_strtoupper' => [ValueError::class],
        'mb_strwidth' => [ValueError::class],
        'mb_substr' => [ValueError::class],
        'mb_substr_count' => [ValueError::class],

        // Array functions
        'array_rand' => [ValueError::class],
        'array_multisort' => [ValueError::class],

        // Random functions
        'random_int' => [RandomException::class],
        'random_bytes' => [RandomException::class],

        // Intl functions
        'intlcal_create_instance' => [IntlException::class],
        'intlcal_from_date_time' => [IntlException::class],
        'intlcal_get_keyword_values_for_locale' => [IntlException::class],
        'intlgregcal_create_instance' => [IntlException::class],
        'intltz_create_default' => [IntlException::class],
        'intltz_create_enumeration' => [IntlException::class],
        'intltz_create_time_zone' => [IntlException::class],
        'intltz_from_date_time_zone' => [IntlException::class],
        'intltz_get_canonical_id' => [IntlException::class],
        'intltz_get_id_for_windows_id' => [IntlException::class],
        'intltz_get_region' => [IntlException::class],
        'intltz_get_tz_data_version' => [IntlException::class],
        'intltz_get_windows_id' => [IntlException::class],

        // PDO functions
        'pdo_drivers' => [PDOException::class],

        // Socket functions
        'socket_create' => [SocketException::class],
        'socket_create_listen' => [SocketException::class],
        'socket_create_pair' => [SocketException::class],
        'socket_accept' => [SocketException::class],
        'socket_addrinfo_bind' => [SocketException::class],
        'socket_addrinfo_connect' => [SocketException::class],
        'socket_addrinfo_explain' => [SocketException::class],
        'socket_addrinfo_lookup' => [SocketException::class],
        'socket_bind' => [SocketException::class],
        'socket_connect' => [SocketException::class],
        'socket_export_stream' => [SocketException::class],
        'socket_get_option' => [SocketException::class],
        'socket_getpeername' => [SocketException::class],
        'socket_getsockname' => [SocketException::class],
        'socket_import_stream' => [SocketException::class],
        'socket_listen' => [SocketException::class],
        'socket_read' => [SocketException::class],
        'socket_recv' => [SocketException::class],
        'socket_recvfrom' => [SocketException::class],
        'socket_recvmsg' => [SocketException::class],
        'socket_send' => [SocketException::class],
        'socket_sendmsg' => [SocketException::class],
        'socket_sendto' => [SocketException::class],
        'socket_set_block' => [SocketException::class],
        'socket_set_nonblock' => [SocketException::class],
        'socket_set_option' => [SocketException::class],
        'socket_shutdown' => [SocketException::class],
        'socket_write' => [SocketException::class],
    ];

    /**
     * Get exceptions that a built-in function can throw
     *
     * @param string $functionName Function name
     *
     * @return string[]|null Array of exception class names, or null if function is not tracked
     */
    public static function getThrows(string $functionName): ?array
    {
        return self::FUNCTION_THROWS_MAP[$functionName] ?? null;
    }

    /**
     * Check if a function is tracked as potentially throwing exceptions
     *
     * @param string $functionName Function name
     *
     * @return bool True if function can throw exceptions
     */
    public static function canThrow(string $functionName): bool
    {
        return isset(self::FUNCTION_THROWS_MAP[$functionName]);
    }

    /**
     * Get all tracked functions
     *
     * @return string[] Array of function names
     */
    public static function getAllFunctions(): array
    {
        return array_keys(self::FUNCTION_THROWS_MAP);
    }
}
