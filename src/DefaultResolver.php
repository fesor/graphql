<?php

namespace Fesor\GraphQL;

use Fesor\GraphQL\Exception\UnableToResolveArgument;
use GraphQL\Type\Definition\ResolveInfo;

final class DefaultResolver
{
    /**
     * @var \ReflectionMethod[]
     */
    static $invokeArgumentsMap = [];

    public function __invoke($source, $args, ResolveInfo $info)
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
                return $this->invoke($source, $accessor, $args, $info);
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

    private function invoke($source, $accessor, array $args, ResolveInfo $info)
    {
        $sourceClass = get_class($source);
        $methodName =  $sourceClass. '::' . $accessor;
        if (!isset(self::$invokeArgumentsMap[$methodName])) {
            $accessorReflection = new \ReflectionMethod($source, $accessor);
            self::$invokeArgumentsMap[$methodName] = array_reduce($accessorReflection->getParameters(), function (array $invokeMap, \ReflectionParameter $parameter) use ($args) {
                $availableArgs = array_keys($args);
                $parameterName = $parameter->getName();
                $parameterType = $parameter->getType();
                if ($parameterType && $parameterType->getName() === ResolveInfo::class) {
                    $availableArgs[] = $parameterName;
                    $invokeMap['resolveInfoArg'] = $parameterName;
                }

                if (!in_array($parameterName, $availableArgs)) {
                    if (!$parameterType || !$parameterType->allowsNull()) {
                        throw new UnableToResolveArgument($parameterName);
                    }

                    return $invokeMap;
                }

                $invokeMap['args'][] = $parameterName;

                return $invokeMap;
            }, [
                'resolveInfoArg' => null,
                'args' => [],
            ]);
        }

        ['resolveInfoArg' => $resolveInfoArg, 'args' => $argsOrder] = self::$invokeArgumentsMap[$methodName];

        if ($resolveInfoArg) {
            $args[$resolveInfoArg] = $info;
        }

        $invokeArgs = [];
        foreach ($argsOrder as $argName) {
            $invokeArgs[] = $args[$argName];
        }

        return $source->$accessor(...$invokeArgs);
    }
}
