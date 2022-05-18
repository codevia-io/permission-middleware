<?php

namespace Codevia\PermissionMiddleware;

abstract class PermissionList
{
    private int $defaultLevel;

    public function __construct(int $defaultLevel)
    {
        $this->defaultLevel = $defaultLevel;
    }

    public function getDefaultLevel(): int
    {
        return $this->defaultLevel;
    }

    /**
     * Check if masks constants are bitwise valid.
     */
    public function checkValidity(): void
    {
        // Get all contants
        $reflection = new \ReflectionClass($this);
        $constants = $reflection->getConstants();

        // Check if all constants are integers
        foreach ($constants as $key => $constant) {
            if (!is_int($constant)) {
                throw new \InvalidArgumentException(
                    "PermissionList constant $key is not an integer"
                );
            }
        }

        // Sort constants by value
        asort($constants, SORT_NUMERIC);

        // Check if all values are bitwise
        $lastValue = 0.5;
        foreach ($constants as $key => $constant) {
            if ($constant != $lastValue * 2) {
                throw new \InvalidArgumentException(
                    "PermissionList constant $key is not a bitwise"
                );
            }
            $lastValue = $constant;
        }
    }
}
