<?php

declare(strict_types=1);

namespace Vix\ExceptionInspector\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vix\ExceptionInspector\Analyzer;

/**
 * Test conditional built-in function throws behavior
 */
final class ConditionalBuiltinThrowsTest extends TestCase
{
    private const string FIXTURE_DIR = __DIR__ . '/../Fixtures';

    #[Test]
    public function it_does_not_flag_json_decode_without_throw_flag(): void
    {
        $analyzer = new Analyzer();
        $file = self::FIXTURE_DIR . '/ConditionalBuiltinThrows.php';
        $result = $analyzer->analyze($file, false);

        // Find the specific method
        $errors = $this->getErrorsForMethod($result, 'jsonDecodeWithoutFlag');

        // Should have NO errors - no flag means no exception
        self::assertEmpty($errors, 'json_decode without JSON_THROW_ON_ERROR should not require @throws');
    }

    #[Test]
    public function it_flags_json_decode_with_throw_flag_missing_throws(): void
    {
        $analyzer = new Analyzer();
        $file = self::FIXTURE_DIR . '/ConditionalBuiltinThrows.php';
        $result = $analyzer->analyze($file, false);

        $errors = $this->getErrorsForMethod($result, 'jsonDecodeWithFlagMissingThrows');

        // Should have error - flag is present but @throws is missing
        self::assertNotEmpty($errors, 'json_decode with JSON_THROW_ON_ERROR should require @throws');
        self::assertStringContainsString('JsonException', $errors[0]['message']);
    }

    #[Test]
    public function it_accepts_json_decode_with_throw_flag_and_correct_throws(): void
    {
        $analyzer = new Analyzer();
        $file = self::FIXTURE_DIR . '/ConditionalBuiltinThrows.php';
        $result = $analyzer->analyze($file, false);

        $errors = $this->getErrorsForMethod($result, 'jsonDecodeWithFlagCorrect');

        // Should have NO errors - flag is present and @throws is correct
        self::assertEmpty($errors, 'json_decode with JSON_THROW_ON_ERROR and @throws should be valid');
    }

    #[Test]
    public function it_does_not_flag_json_encode_without_throw_flag(): void
    {
        $analyzer = new Analyzer();
        $file = self::FIXTURE_DIR . '/ConditionalBuiltinThrows.php';
        $result = $analyzer->analyze($file, false);

        $errors = $this->getErrorsForMethod($result, 'jsonEncodeWithoutFlag');

        // Should have NO errors - no flag means no exception
        self::assertEmpty($errors, 'json_encode without JSON_THROW_ON_ERROR should not require @throws');
    }

    #[Test]
    public function it_flags_json_encode_with_throw_flag_missing_throws(): void
    {
        $analyzer = new Analyzer();
        $file = self::FIXTURE_DIR . '/ConditionalBuiltinThrows.php';
        $result = $analyzer->analyze($file, false);

        $errors = $this->getErrorsForMethod($result, 'jsonEncodeWithFlagMissingThrows');

        // Should have error - flag is present but @throws is missing
        self::assertNotEmpty($errors, 'json_encode with JSON_THROW_ON_ERROR should require @throws');
        self::assertStringContainsString('JsonException', $errors[0]['message']);
    }

    #[Test]
    public function it_accepts_json_encode_with_throw_flag_and_correct_throws(): void
    {
        $analyzer = new Analyzer();
        $file = self::FIXTURE_DIR . '/ConditionalBuiltinThrows.php';
        $result = $analyzer->analyze($file, false);

        $errors = $this->getErrorsForMethod($result, 'jsonEncodeWithFlagCorrect');

        // Should have NO errors - flag is present and @throws is correct
        self::assertEmpty($errors, 'json_encode with JSON_THROW_ON_ERROR and @throws should be valid');
    }

    #[Test]
    public function it_detects_throw_flag_in_bitwise_or(): void
    {
        $analyzer = new Analyzer();
        $file = self::FIXTURE_DIR . '/ConditionalBuiltinThrows.php';
        $result = $analyzer->analyze($file, false);

        $errors = $this->getErrorsForMethod($result, 'jsonEncodeWithBitwiseOr');

        // Should have error - JSON_THROW_ON_ERROR is present in bitwise OR
        self::assertNotEmpty($errors, 'json_encode with bitwise OR containing JSON_THROW_ON_ERROR should require @throws');
    }

    #[Test]
    public function it_does_not_flag_bitwise_or_without_throw_flag(): void
    {
        $analyzer = new Analyzer();
        $file = self::FIXTURE_DIR . '/ConditionalBuiltinThrows.php';
        $result = $analyzer->analyze($file, false);

        $errors = $this->getErrorsForMethod($result, 'jsonEncodeWithBitwiseOrNoThrow');

        // Should have NO errors - JSON_THROW_ON_ERROR is not in the flags
        self::assertEmpty($errors, 'json_encode with bitwise OR without JSON_THROW_ON_ERROR should not require @throws');
    }

    #[Test]
    public function it_detects_numeric_throw_flag(): void
    {
        $analyzer = new Analyzer();
        $file = self::FIXTURE_DIR . '/ConditionalBuiltinThrows.php';
        $result = $analyzer->analyze($file, false);

        $errors = $this->getErrorsForMethod($result, 'jsonEncodeWithNumericFlag');

        // Should have error - numeric value 4 is JSON_THROW_ON_ERROR
        self::assertNotEmpty($errors, 'json_encode with numeric flag 4 should require @throws');
    }

    #[Test]
    public function it_is_conservative_with_variable_flags(): void
    {
        $analyzer = new Analyzer();
        $file = self::FIXTURE_DIR . '/ConditionalBuiltinThrows.php';
        $result = $analyzer->analyze($file, false);

        $errors = $this->getErrorsForMethod($result, 'jsonDecodeWithVariableFlag');

        // Should have error - cannot determine if flag is set, so be conservative
        // Actually this test expects to find a missing throws error
        self::assertNotEmpty($errors, 'json_decode with variable flag should conservatively require @throws');
    }

    #[Test]
    public function it_detects_throw_flag_in_complex_bitwise_or(): void
    {
        $analyzer = new Analyzer();
        $file = self::FIXTURE_DIR . '/ConditionalBuiltinThrows.php';
        $result = $analyzer->analyze($file, false);

        $errors = $this->getErrorsForMethod($result, 'jsonDecodeWithComplexBitwiseOr');

        // Should have error - JSON_THROW_ON_ERROR is in the complex expression
        self::assertNotEmpty($errors, 'json_decode with complex bitwise OR containing JSON_THROW_ON_ERROR should require @throws');
    }

    /**
     * Get errors for a specific method
     *
     * @param array{files: array<array{file: string, errors: array<array{line: int, message: string}>}>} $result Analyzer result
     * @param string $methodName Method name to filter for
     *
     * @return array<array{line: int, message: string}> Errors for the method
     */
    private function getErrorsForMethod(array $result, string $methodName): array
    {
        if (empty($result['files'])) {
            return [];
        }

        $allErrors = $result['files'][0]['errors'] ?? [];
        $filteredErrors = [];

        // Get the file content to find method line ranges
        $file = self::FIXTURE_DIR . '/ConditionalBuiltinThrows.php';
        $content = file_get_contents($file);

        if ($content === false) {
            return [];
        }

        // Find the method in the file
        $pattern = '/public function ' . preg_quote($methodName, '/') . '\([^)]*\)[^{]*\{/';

        if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE) === 1) {
            $methodStart = substr_count($content, "\n", 0, $matches[0][1]) + 1;

            // Find the end of the method (next method or class end)
            $afterMethod = substr($content, $matches[0][1]);
            $braceCount = 0;
            $inMethod = false;
            $methodEnd = $methodStart;

            for ($i = 0, $iMax = strlen($afterMethod); $i < $iMax; $i++) {
                if ($afterMethod[$i] === '{') {
                    $braceCount++;
                    $inMethod = true;
                } elseif ($afterMethod[$i] === '}') {
                    $braceCount--;

                    if ($inMethod && $braceCount === 0) {
                        $methodEnd = $methodStart + substr_count($afterMethod, "\n", 0, $i);

                        break;
                    }
                }
            }

            // Filter errors within method range
            foreach ($allErrors as $error) {
                if ($error['line'] >= $methodStart && $error['line'] <= $methodEnd) {
                    $filteredErrors[] = $error;
                }
            }
        }

        return $filteredErrors;
    }
}
