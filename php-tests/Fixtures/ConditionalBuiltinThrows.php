<?php

declare(strict_types=1);

namespace Vix\ExceptionInspector\Tests\Fixtures;

use JsonException;

class ConditionalBuiltinThrows
{
    /**
     * json_decode WITHOUT JSON_THROW_ON_ERROR - should NOT require @throws
     *
     * @return mixed
     */
    public function jsonDecodeWithoutFlag(): mixed
    {
        $json = '{"test": "value"}';

        return json_decode($json, true);
    }

    /**
     * json_decode WITH JSON_THROW_ON_ERROR - should require @throws
     * This should be flagged as missing @throws
     *
     * @return mixed
     */
    public function jsonDecodeWithFlagMissingThrows(): mixed
    {
        $json = '{"test": "value"}';

        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * json_decode WITH JSON_THROW_ON_ERROR and correct @throws
     *
     * @return mixed
     *
     * @throws JsonException
     */
    public function jsonDecodeWithFlagCorrect(): mixed
    {
        $json = '{"test": "value"}';

        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * json_encode WITHOUT JSON_THROW_ON_ERROR - should NOT require @throws
     *
     * @param mixed $data Data to encode
     *
     * @return string|false
     */
    public function jsonEncodeWithoutFlag(mixed $data): string|false
    {
        return json_encode($data);
    }

    /**
     * json_encode WITH JSON_THROW_ON_ERROR - should require @throws
     * This should be flagged as missing @throws
     *
     * @param mixed $data Data to encode
     *
     * @return string
     */
    public function jsonEncodeWithFlagMissingThrows(mixed $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    /**
     * json_encode WITH JSON_THROW_ON_ERROR and correct @throws
     *
     * @param mixed $data Data to encode
     *
     * @return string
     *
     * @throws JsonException
     */
    public function jsonEncodeWithFlagCorrect(mixed $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    /**
     * json_encode with bitwise OR flags including JSON_THROW_ON_ERROR
     * This should be flagged as missing @throws
     *
     * @param mixed $data Data to encode
     *
     * @return string
     */
    public function jsonEncodeWithBitwiseOr(mixed $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * json_encode with bitwise OR flags without JSON_THROW_ON_ERROR
     * This should NOT require @throws
     *
     * @param mixed $data Data to encode
     *
     * @return string|false
     */
    public function jsonEncodeWithBitwiseOrNoThrow(mixed $data): string|false
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * json_encode with numeric flag value 4 (JSON_THROW_ON_ERROR)
     * This should be flagged as missing @throws
     *
     * @param mixed $data Data to encode
     *
     * @return string
     */
    public function jsonEncodeWithNumericFlag(mixed $data): string
    {
        return json_encode($data, 4); // 4 = JSON_THROW_ON_ERROR
    }

    /**
     * json_decode with variable flag - cannot determine
     * Should be conservative and require @throws
     *
     * @param int $flags Flags to use
     *
     * @return mixed
     */
    public function jsonDecodeWithVariableFlag(int $flags): mixed
    {
        $json = '{"test": "value"}';

        return json_decode($json, true, 512, $flags);
    }

    /**
     * json_decode with complex bitwise OR containing JSON_THROW_ON_ERROR
     * This should be flagged as missing @throws
     *
     * @return mixed
     */
    public function jsonDecodeWithComplexBitwiseOr(): mixed
    {
        $json = '{"test": "value"}';

        return json_decode(
            $json,
            true,
            512,
            JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_IGNORE
        );
    }
}
