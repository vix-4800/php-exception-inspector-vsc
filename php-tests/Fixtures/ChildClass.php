<?php

namespace Fixtures;

use Exception;
use RuntimeException;

class ChildClass extends ParentClass
{
    /**
     * This method calls parent method with @throws but doesn't document it
     */
    public function callsParentWithoutDocumentation(): void
    {
        // Should trigger error: missing @throws RuntimeException
        $this->methodWithThrows();
    }

    /**
     * This method properly documents parent's exception
     * @throws RuntimeException
     */
    public function callsParentWithProperDocumentation(): void
    {
        // Should be OK
        $this->methodWithThrows();
    }

    /**
     * Calls multiple parent methods
     */
    public function callsMultipleParentMethods(): void
    {
        // Should trigger errors for RuntimeException
        $this->methodWithThrows();
    }

    /**
     * Calls protected parent method
     */
    public function callsProtectedParentMethod(): void
    {
        // Should trigger errors for RuntimeException and Exception
        $this->protectedMethodWithMultipleThrows();
    }

    /**
     * Properly documents all exceptions from parent methods
     * @throws RuntimeException
     * @throws Exception
     */
    public function callsProtectedParentMethodWithDocs(): void
    {
        // Should be OK
        $this->protectedMethodWithMultipleThrows();
    }
}
