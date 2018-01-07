<?php

namespace Fesor\GraphQL\Exception;

class UnableToResolveArgument extends RuntimeException
{
    public function __construct(string $argumentName)
    {
        parent::__construct(sprintf("Unable to resolve value for '$argumentName'"));
    }
}