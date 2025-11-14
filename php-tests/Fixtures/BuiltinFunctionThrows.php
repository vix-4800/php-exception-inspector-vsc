<?php

declare(strict_types=1);

namespace Vix\ExceptionInspector\Tests\Fixtures;

use JsonException;

class BuiltinFunctionThrows
{
    /**
     * This method uses json_decode but doesn't document JsonException
     *
     * @return mixed
     */
    public function missingJsonException(): mixed
    {
        $json = '{"test": "value"}';

        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * This method correctly documents JsonException
     *
     * @return mixed
     *
     * @throws JsonException
     */
    public function correctlyDocumentedJsonException(): mixed
    {
        $json = '{"test": "value"}';

        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * This method catches JsonException so no @throws needed
     *
     * @return mixed
     */
    public function caughtJsonException(): mixed
    {
        $json = '{"test": "value"}';

        try {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }
    }

    /**
     * This method uses json_encode without documenting exception
     *
     * @param mixed $data Data to encode
     *
     * @return string
     */
    public function missingJsonEncodeException(mixed $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    /**
     * This method correctly documents json_encode exception
     *
     * @param mixed $data Data to encode
     *
     * @return string
     *
     * @throws JsonException
     */
    public function correctlyDocumentedJsonEncode(mixed $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    /**
     * This method uses multiple functions that can throw
     *
     * @param mixed $data Data to encode
     *
     * @return mixed
     */
    public function multipleFunctionsThrows(mixed $data): mixed
    {
        $encoded = json_encode($data, JSON_THROW_ON_ERROR);

        return json_decode($encoded, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * This method uses random_int without documenting exception
     *
     * @return int
     */
    public function missingRandomException(): int
    {
        return random_int(1, 100);
    }

    /**
     * This method correctly documents Random\RandomException
     *
     * @return int
     *
     * @throws \Random\RandomException
     */
    public function correctlyDocumentedRandomException(): int
    {
        return random_int(1, 100);
    }
}
