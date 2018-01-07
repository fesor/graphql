<?php

namespace Fesor\GraphQL;

use GraphQL\Type\Definition\ResolveInfo;

final class MethodResolver
{
    private $service;
    private $method;

    public function __construct($service, string $method)
    {
        $this->service = $service;
        $this->method = $method;
    }

    public function __invoke($source, $args, ResolveInfo $info)
    {
        return $this->service->{$this->method}($source, $args, $info);
    }
}
