<?php

declare(strict_types=1);

namespace Vix\ExceptionInspector\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Vix\ExceptionInspector\Analyzer;

final class InheritanceAnalyzerTest extends TestCase
{
    private Analyzer $analyzer;
    private string $fixturesDir;

    protected function setUp(): void
    {
        $this->analyzer = new Analyzer();
        $this->fixturesDir = __DIR__ . '/../Fixtures';
    }

    public function testChildClassCallingParentMethodWithoutDocumentation(): void
    {
        $result = $this->analyzer->analyze(
            $this->fixturesDir . '/ChildClass.php',
            useProjectWideAnalysis: true
        );

        $this->assertNotEmpty($result['files'], 'Should have analyzed files');

        $childClassFile = null;
        foreach ($result['files'] as $file) {
            if (str_contains($file['file'], 'ChildClass.php')) {
                $childClassFile = $file;
                break;
            }
        }

        $this->assertNotNull($childClassFile, 'ChildClass.php should be in results');

        $foundMissingThrows = false;
        foreach ($childClassFile['errors'] as $error) {
            if (
                ($error['type'] === 'missing_throws' || $error['type'] === 'undeclared_throw_from_call') &&
                str_contains($error['message'], 'RuntimeException') &&
                $error['line'] >= 13 && $error['line'] <= 17
            ) {
                $foundMissingThrows = true;
                break;
            }
        }

        $this->assertTrue(
            $foundMissingThrows,
            'Should detect missing @throws RuntimeException in callsParentWithoutDocumentation'
        );
    }

    public function testChildClassCallingParentMethodWithProperDocumentation(): void
    {
        $result = $this->analyzer->analyze(
            $this->fixturesDir . '/ChildClass.php',
            useProjectWideAnalysis: true
        );

        $childClassFile = null;
        foreach ($result['files'] as $file) {
            if (str_contains($file['file'], 'ChildClass.php')) {
                $childClassFile = $file;
                break;
            }
        }

        $this->assertNotNull($childClassFile);

        $hasErrorInProperMethod = false;
        foreach ($childClassFile['errors'] as $error) {
            if (
                $error['line'] >= 23 && $error['line'] <= 27 &&
                $error['type'] === 'missing_throws'
            ) {
                $hasErrorInProperMethod = true;
                break;
            }
        }

        $this->assertFalse(
            $hasErrorInProperMethod,
            'Should NOT report errors when @throws is properly documented'
        );
    }

    public function testChildClassCallingMultipleParentMethods(): void
    {
        $result = $this->analyzer->analyze(
            $this->fixturesDir . '/ChildClass.php',
            useProjectWideAnalysis: true
        );

        $childClassFile = null;
        foreach ($result['files'] as $file) {
            if (str_contains($file['file'], 'ChildClass.php')) {
                $childClassFile = $file;
                break;
            }
        }

        $this->assertNotNull($childClassFile);

        // callsMultipleParentMethods should have errors for both RuntimeException and Exception
        $errorsInMultipleMethods = [];
        foreach ($childClassFile['errors'] as $error) {
            if (
                $error['line'] >= 32 && $error['line'] <= 37 &&
                ($error['type'] === 'missing_throws' || $error['type'] === 'undeclared_throw_from_call')
            ) {
                $errorsInMultipleMethods[] = $error['exception'];
            }
        }

        $this->assertGreaterThanOrEqual(
            1,
            count($errorsInMultipleMethods),
            'Should detect missing @throws for both RuntimeException and Exception'
        );
    }

    public function testChildClassCallingProtectedParentMethod(): void
    {
        $result = $this->analyzer->analyze(
            $this->fixturesDir . '/ChildClass.php',
            useProjectWideAnalysis: true
        );

        $childClassFile = null;
        foreach ($result['files'] as $file) {
            if (str_contains($file['file'], 'ChildClass.php')) {
                $childClassFile = $file;
                break;
            }
        }

        $this->assertNotNull($childClassFile);

        // callsProtectedParentMethod should have errors for RuntimeException and Exception
        $errorsInProtectedMethod = [];
        foreach ($childClassFile['errors'] as $error) {
            if (
                $error['line'] >= 42 && $error['line'] <= 46 &&
                ($error['type'] === 'missing_throws' || $error['type'] === 'undeclared_throw_from_call')
            ) {
                $errorsInProtectedMethod[] = $error['exception'];
            }
        }

        $this->assertGreaterThanOrEqual(
            2,
            count($errorsInProtectedMethod),
            'Should detect missing @throws for protected parent method exceptions'
        );
    }
}
