<?php

namespace Fesor\GraphQL;

use GraphQL\Type\Definition\ResolveInfo;

final class DefaultResolver
{
    public function __invoke($source, $args, $context, ResolveInfo $info)
    {
        $fieldName = $info->fieldName;
        if (is_array($source) || $source instanceof \ArrayAccess) {
            return $source[$fieldName];
        }

        if (!is_object($source)) {
            return null;
        }

        if (property_exists($source, $fieldName)) {
            return $source->$fieldName;
        }

        foreach ($this->accessors($fieldName) as $accessor) {
            if (method_exists($source, $accessor)) {
                return $source->$accessor($args);
            }
        }

        return null;
    }
    
    private function accessors(string $fieldName)
    {
        return [
            'get' . ucfirst($fieldName),
            $fieldName
        ];
    }
}
