<?php

declare(strict_types=1);

namespace Vix\ExceptionInspector\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Vix\ExceptionInspector\Analyzer;

/**
 * Tests for built-in PHP function exception detection
 */
final class BuiltinFunctionAnalyzerTest extends TestCase
{
    /**
     * Test that json_decode without @throws JsonException is detected
     *
     * @return void
     *
     * @throws \JsonException
     */
    public function testDetectsMissingJsonDecodeException(): void
    {
        $analyzer = new Analyzer();
        $testFile = __DIR__ . '/../Fixtures/BuiltinFunctionThrows.php';

        $result = $analyzer->analyze($testFile, false);

        $errors = $result['files'][0]['errors'] ?? [];

        $jsonDecodeError = null;

        foreach ($errors as $error) {
            if (
                $error['exception'] === 'JsonException'
                && str_contains($error['message'], 'json_decode')
                && $error['function'] === 'missingJsonException'
            ) {
                $jsonDecodeError = $error;

                break;
            }
        }

        self::assertNotNull(
            $jsonDecodeError,
            'Should detect missing @throws JsonException for json_decode()'
        );
        self::assertSame('undeclared_throw', $jsonDecodeError['type']);
    }

    /**
     * Test that correctly documented json_decode has no error
     *
     * @return void
     *
     * @throws \JsonException
     */
    public function testCorrectlyDocumentedJsonDecodeHasNoError(): void
    {
        $analyzer = new Analyzer();
        $testFile = __DIR__ . '/../Fixtures/BuiltinFunctionThrows.php';

        $result = $analyzer->analyze($testFile, false);

        $errors = $result['files'][0]['errors'] ?? [];

        foreach ($errors as $error) {
            if (
                $error['exception'] === 'JsonException'
                && $error['function'] === 'correctlyDocumentedJsonException'
            ) {
                self::fail('Should not report error for correctly documented json_decode()');
            }
        }

        self::assertTrue(true);
    }

    /**
     * Test that caught JsonException doesn't require @throws
     *
     * @return void
     *
     * @throws \JsonException
     */
    public function testCaughtJsonExceptionHasNoError(): void
    {
        $analyzer = new Analyzer();
        $testFile = __DIR__ . '/../Fixtures/BuiltinFunctionThrows.php';

        $result = $analyzer->analyze($testFile, false);

        $errors = $result['files'][0]['errors'] ?? [];

        foreach ($errors as $error) {
            if (
                $error['exception'] === 'JsonException'
                && $error['function'] === 'caughtJsonException'
            ) {
                self::fail('Should not report error for caught JsonException');
            }
        }

        self::assertTrue(true);
    }

    /**
     * Test that json_encode without @throws is detected
     *
     * @return void
     *
     * @throws \JsonException
     */
    public function testDetectsMissingJsonEncodeException(): void
    {
        $analyzer = new Analyzer();
        $testFile = __DIR__ . '/../Fixtures/BuiltinFunctionThrows.php';

        $result = $analyzer->analyze($testFile, false);

        $errors = $result['files'][0]['errors'] ?? [];

        $jsonEncodeError = null;

        foreach ($errors as $error) {
            if (
                $error['exception'] === 'JsonException'
                && str_contains($error['message'], 'json_encode')
                && $error['function'] === 'missingJsonEncodeException'
            ) {
                $jsonEncodeError = $error;

                break;
            }
        }

        self::assertNotNull(
            $jsonEncodeError,
            'Should detect missing @throws JsonException for json_encode()'
        );
    }

    /**
     * Test that multiple function calls are detected
     *
     * @return void
     *
     * @throws \JsonException
     */
    public function testDetectsMultipleFunctionThrows(): void
    {
        $analyzer = new Analyzer();
        $testFile = __DIR__ . '/../Fixtures/BuiltinFunctionThrows.php';

        $result = $analyzer->analyze($testFile, false);

        $errors = $result['files'][0]['errors'] ?? [];

        $encodeError = null;
        $decodeError = null;

        foreach ($errors as $error) {
            if (
                $error['exception'] === 'JsonException'
                && $error['function'] === 'multipleFunctionsThrows'
            ) {
                if (str_contains($error['message'], 'json_encode')) {
                    $encodeError = $error;
                } elseif (str_contains($error['message'], 'json_decode')) {
                    $decodeError = $error;
                }
            }
        }

        self::assertNotNull($encodeError, 'Should detect json_encode() exception');
        self::assertNotNull($decodeError, 'Should detect json_decode() exception');
    }

    /**
     * Test that random_int without @throws is detected
     *
     * @return void
     *
     * @throws \JsonException
     */
    public function testDetectsMissingRandomException(): void
    {
        $analyzer = new Analyzer();
        $testFile = __DIR__ . '/../Fixtures/BuiltinFunctionThrows.php';

        $result = $analyzer->analyze($testFile, false);

        $errors = $result['files'][0]['errors'] ?? [];

        $randomError = null;

        foreach ($errors as $error) {
            if (
                str_contains($error['exception'], 'RandomException')
                && str_contains($error['message'], 'random_int')
                && $error['function'] === 'missingRandomException'
            ) {
                $randomError = $error;

                break;
            }
        }

        self::assertNotNull(
            $randomError,
            'Should detect missing @throws Random\RandomException for random_int()'
        );
    }

    /**
     * Test that correctly documented random_int has no error
     *
     * @return void
     *
     * @throws \JsonException
     */
    public function testCorrectlyDocumentedRandomExceptionHasNoError(): void
    {
        $analyzer = new Analyzer();
        $testFile = __DIR__ . '/../Fixtures/BuiltinFunctionThrows.php';

        $result = $analyzer->analyze($testFile, false);

        $errors = $result['files'][0]['errors'] ?? [];

        foreach ($errors as $error) {
            if (
                str_contains($error['exception'], 'RandomException')
                && $error['function'] === 'correctlyDocumentedRandomException'
            ) {
                self::fail('Should not report error for correctly documented random_int()');
            }
        }

        self::assertTrue(true);
    }
}
