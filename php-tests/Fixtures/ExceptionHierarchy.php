<?php

namespace Vix\ExceptionInspector\Fixtures;

use InvalidArgumentException;
use RuntimeException;
use Exception;
use LogicException;

/**
 * Custom exception hierarchy for testing
 */
class CustomException extends Exception {}
class ValidationException extends CustomException {}
class DatabaseException extends RuntimeException {}
class NetworkException extends RuntimeException {}

class ExceptionHierarchyTest
{
    /**
     * Base exception declared, derived exception thrown - should be OK
     * @throws Exception
     */
    public function throwsDerivedButDeclaresBase(): void
    {
        throw new InvalidArgumentException('Invalid argument');
    }

    /**
     * Derived exception declared, base exception thrown - should report missing @throws
     * @throws InvalidArgumentException
     */
    public function throwsBaseButDeclaresDerived(): void
    {
        throw new Exception('Generic exception'); // Should report missing @throws Exception
    }

    /**
     * Multiple levels of hierarchy - custom exception
     * @throws CustomException
     */
    public function throwsCustomHierarchy(): void
    {
        throw new ValidationException('Validation failed'); // OK, ValidationException extends CustomException
    }

    /**
     * Sibling exceptions in hierarchy
     * @throws InvalidArgumentException
     */
    public function throwsSiblingException(): void
    {
        throw new LogicException('Logic error'); // Should report missing - siblings, not parent-child
    }

    /**
     * Multiple exceptions in hierarchy
     * @throws RuntimeException
     */
    public function throwsMultipleInHierarchy(): void
    {
        if (rand(0, 1)) {
            throw new DatabaseException('Database error'); // OK
        } else {
            throw new NetworkException('Network error'); // OK
        }
    }

    /**
     * No @throws tag, but throws derived exception
     */
    public function noDocButThrowsDerived(): void
    {
        throw new ValidationException('Validation failed'); // Should report missing @throws
    }

    /**
     * Declares multiple base exceptions
     * @throws RuntimeException
     * @throws Exception
     */
    public function decluresMultipleBases(): void
    {
        if (rand(0, 1)) {
            throw new DatabaseException('Database error'); // OK - covered by RuntimeException
        } else {
            throw new InvalidArgumentException('Invalid'); // OK - covered by Exception
        }
    }

    /**
     * Method calls that propagate exceptions
     * @throws CustomException
     */
    public function propagatesFromMethod(): void
    {
        $this->throwsCustomHierarchy(); // OK - propagates CustomException
    }

    /**
     * Undocumented propagation
     */
    public function undocumentedPropagation(): void
    {
        $this->throwsDerivedButDeclaresBase(); // Should report missing @throws Exception
    }

    /**
     * Catches base exception, still throws derived
     * @throws ValidationException
     */
    public function catchesBaseThrownsDerived(): void
    {
        try {
            // Some code
            if (rand(0, 1)) {
                throw new CustomException('Custom');
            }
        } catch (Exception $e) {
            // Caught base exception
        }

        throw new ValidationException('Validation failed'); // OK
    }

    /**
     * Catches derived, rethrows base
     * @throws Exception
     */
    public function catchesDerivedRethrowsBase(): void
    {
        try {
            throw new ValidationException('Validation failed');
        } catch (ValidationException $e) {
            throw new Exception('Wrapped', 0, $e); // OK
        }
    }

    /**
     * Multiple catch blocks with hierarchy
     * @throws RuntimeException
     */
    public function multipleCatchHierarchy(): void
    {
        try {
            $this->throwsMultipleInHierarchy();
        } catch (DatabaseException $e) {
            // Handle database exception
            throw $e; // OK - DatabaseException is RuntimeException
        } catch (NetworkException $e) {
            // Handle network exception
            throw new RuntimeException('Network issue', 0, $e); // OK
        }
    }

    /**
     * Catch base, throw derived
     */
    public function catchBaseThrowDerived(): void
    {
        try {
            $this->throwsDerivedButDeclaresBase();
        } catch (Exception $e) {
            throw new InvalidArgumentException('Invalid', 0, $e); // Should report missing @throws
        }
    }

    /**
     * Interface type hint with exception hierarchy
     * @throws RuntimeException
     */
    public function interfaceTypeHint(): void
    {
        throw new DatabaseException('Database error'); // OK
    }

    /**
     * Abstract base exception
     * @throws CustomException
     */
    public function abstractBaseException(): void
    {
        if (rand(0, 1)) {
            throw new ValidationException('Validation'); // OK
        } else {
            throw new CustomException('Custom'); // OK
        }
    }
}
