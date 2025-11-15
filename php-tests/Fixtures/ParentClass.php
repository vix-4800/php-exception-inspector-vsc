<?php

namespace Fixtures;

use Exception;
use RuntimeException;

class ParentClass
{
    /**
     * @throws RuntimeException
     */
    public function methodWithThrows(): void
    {
        throw new RuntimeException('Error in parent');
    }

    /**
     * @throws RuntimeException
     * @throws Exception
     */
    protected function protectedMethodWithMultipleThrows(): void
    {
        if (rand(0, 1)) {
            throw new RuntimeException('Runtime error');
        }
        throw new Exception('General error');
    }
}
