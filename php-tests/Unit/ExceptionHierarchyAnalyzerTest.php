<?php

declare(strict_types=1);

namespace Vix\ExceptionInspector\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Vix\ExceptionInspector\Analyzer;

final class ExceptionHierarchyAnalyzerTest extends TestCase
{
    private Analyzer $analyzer;
    private string $fixturesPath;

    protected function setUp(): void
    {
        $this->analyzer = new Analyzer();
        $this->fixturesPath = __DIR__ . '/../Fixtures/ExceptionHierarchy.php';
    }

    public function testThrowsDerivedButDeclaresBase(): void
    {
        $result = $this->analyzer->analyze($this->fixturesPath, false);

        // Should be OK - InvalidArgumentException extends Exception
        $errors = $this->getErrorsForMethod($result, 'throwsDerivedButDeclaresBase');
        $this->assertEmpty($errors, 'Throwing derived exception when base is declared should be allowed');
    }

    public function testThrowsBaseButDeclaresDerived(): void
    {
        $result = $this->analyzer->analyze($this->fixturesPath, false);

        // Should report missing @throws Exception
        $errors = $this->getErrorsForMethod($result, 'throwsBaseButDeclaresDerived');
        $this->assertNotEmpty($errors, 'Should report missing @throws for base exception when derived is declared');

        $hasExceptionError = false;
        foreach ($errors as $error) {
            if ($error['type'] === 'missing_throws' &&
                str_contains($error['exception'] ?? '', 'Exception')) {
                $hasExceptionError = true;
                break;
            }
        }
        $this->assertTrue($hasExceptionError, 'Should report missing @throws Exception');
    }

    public function testThrowsCustomHierarchy(): void
    {
        $result = $this->analyzer->analyze($this->fixturesPath, false);

        // ValidationException extends CustomException - should be OK
        $errors = $this->getErrorsForMethod($result, 'throwsCustomHierarchy');
        $this->assertEmpty($errors, 'Throwing ValidationException when CustomException is declared should be allowed');
    }

    public function testThrowsSiblingException(): void
    {
        $result = $this->analyzer->analyze($this->fixturesPath, false);

        // LogicException and InvalidArgumentException are siblings - should report error
        $errors = $this->getErrorsForMethod($result, 'throwsSiblingException');
        $this->assertNotEmpty($errors, 'Should report missing @throws for sibling exception');

        $hasLogicExceptionError = false;
        foreach ($errors as $error) {
            if ($error['type'] === 'missing_throws' &&
                str_contains($error['exception'] ?? '', 'LogicException')) {
                $hasLogicExceptionError = true;
                break;
            }
        }
        $this->assertTrue($hasLogicExceptionError, 'Should report missing @throws LogicException');
    }

    public function testThrowsMultipleInHierarchy(): void
    {
        $result = $this->analyzer->analyze($this->fixturesPath, false);

        // Both DatabaseException and NetworkException extend RuntimeException - should be OK
        $errors = $this->getErrorsForMethod($result, 'throwsMultipleInHierarchy');
        $this->assertEmpty($errors, 'Multiple derived exceptions covered by base @throws should be allowed');
    }

    public function testNoDocButThrowsDerived(): void
    {
        $result = $this->analyzer->analyze($this->fixturesPath, false);

        // No @throws tag - should report error
        $errors = $this->getErrorsForMethod($result, 'noDocButThrowsDerived');
        $this->assertNotEmpty($errors, 'Should report missing @throws when no documentation exists');
    }

    public function testDeclaresMultipleBases(): void
    {
        $result = $this->analyzer->analyze($this->fixturesPath, false);

        // Both exceptions covered by declared base classes
        $errors = $this->getErrorsForMethod($result, 'decluresMultipleBases');
        $this->assertEmpty($errors, 'Multiple base @throws should cover all derived exceptions');
    }

    public function testPropagatesFromMethod(): void
    {
        $result = $this->analyzer->analyze($this->fixturesPath, true);

        // Calls method that throws CustomException - should be OK
        $errors = $this->getErrorsForMethod($result, 'propagatesFromMethod');
        $this->assertEmpty($errors, 'Documented propagation with correct hierarchy should be allowed');
    }

    public function testUndocumentedPropagation(): void
    {
        $result = $this->analyzer->analyze($this->fixturesPath, true);

        // Calls method that throws Exception but not documented
        $errors = $this->getErrorsForMethod($result, 'undocumentedPropagation');
        $this->assertNotEmpty($errors, 'Should report missing @throws for propagated exceptions');
    }

    public function testCatchesBaseThrownsDerived(): void
    {
        $result = $this->analyzer->analyze($this->fixturesPath, false);

        // Catches base Exception, throws ValidationException - should be OK
        $errors = $this->getErrorsForMethod($result, 'catchesBaseThrownsDerived');
        $this->assertEmpty($errors, 'Throwing derived exception outside catch block should work when documented');
    }

    public function testCatchesDerivedRethrowsBase(): void
    {
        $result = $this->analyzer->analyze($this->fixturesPath, false);

        // Catches derived, throws base - should be OK
        $errors = $this->getErrorsForMethod($result, 'catchesDerivedRethrowsBase');
        $this->assertEmpty($errors, 'Throwing base exception when derived is caught should be allowed');
    }

    public function testMultipleCatchHierarchy(): void
    {
        $result = $this->analyzer->analyze($this->fixturesPath, true);

        // Multiple catch blocks with proper hierarchy handling
        $errors = $this->getErrorsForMethod($result, 'multipleCatchHierarchy');
        $this->assertEmpty($errors, 'Multiple catch blocks with hierarchy should work correctly');
    }

    public function testCatchBaseThrowDerived(): void
    {
        $result = $this->analyzer->analyze($this->fixturesPath, false);

        // Catches base, throws derived but undocumented
        $errors = $this->getErrorsForMethod($result, 'catchBaseThrowDerived');
        $this->assertNotEmpty($errors, 'Should report missing @throws for exception thrown in catch block');
    }

    public function testInterfaceTypeHint(): void
    {
        $result = $this->analyzer->analyze($this->fixturesPath, false);

        // DatabaseException extends RuntimeException - should be OK
        $errors = $this->getErrorsForMethod($result, 'interfaceTypeHint');
        $this->assertEmpty($errors, 'Exception hierarchy should work with interface type hints');
    }

    public function testAbstractBaseException(): void
    {
        $result = $this->analyzer->analyze($this->fixturesPath, false);

        // Both ValidationException and CustomException covered by @throws CustomException
        $errors = $this->getErrorsForMethod($result, 'abstractBaseException');
        $this->assertEmpty($errors, 'Abstract base exception should cover both itself and derived classes');
    }

    /**
     * Helper method to get errors for a specific method
     */
    private function getErrorsForMethod(array $result, string $methodName): array
    {
        if (empty($result['files'])) {
            return [];
        }

        $methodErrors = [];

        foreach ($result['files'] as $file) {
            if (isset($file['errors'])) {
                foreach ($file['errors'] as $error) {
                    // Check if error function field matches method name
                    if (($error['function'] ?? '') === $methodName) {
                        $methodErrors[] = $error;
                    }
                }
            }
        }

        return $methodErrors;
    }
}
