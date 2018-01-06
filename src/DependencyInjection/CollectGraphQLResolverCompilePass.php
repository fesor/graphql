<?php

namespace Fesor\GraphQL\DependencyInjection;

use Fesor\GraphQL\MethodResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

class CollectGraphQLResolverCompilePass implements CompilerPassInterface
{
    const TAG_RESOLVER = 'graphql.resolver';
    const RESOLVER_MAP_ID = 'graphql.resolver_map';

    public function process(ContainerBuilder $container)
    {
        $resolverMap = [];
        $resolverConfigurations = $container->findTaggedServiceIds(self::TAG_RESOLVER);
        foreach ($this->configureResolver($resolverConfigurations) as $resolverId => $resolverDefinition) {
            $serviceId = "graphql_resolver.$resolverId";
            $container->setDefinition($serviceId, $resolverDefinition);
            $resolverMap[$resolverId] = new Reference($serviceId);
        }

        $resolverLocator = new Definition(ServiceLocator::class);
        $resolverLocator->addArgument($resolverMap);
        $resolverLocator->addTag('container.service_locator');
        $resolverLocator->setPrivate(false);
        $container->setDefinition(self::RESOLVER_MAP_ID, $resolverLocator);
    }

    private function configureResolver(array $resolverConfigurations)
    {
        foreach ($resolverConfigurations as $serviceId => $tags) {
            foreach ($tags as $configuration) {
                yield from $this->registerResolver($serviceId, $configuration);
            }
        }
    }

    private function registerResolver(string $serviceId, array $configuration)
    {
        $type = $configuration['type'] ?? 'Query';
        $field = $configuration['field'];
        $method = $configuration['method'];

        $resolverDefinition = new Definition(MethodResolver::class, [
            new Reference($serviceId),
            $method
        ]);
        $resolverDefinition->setPrivate(true);
        $resolverServiceId = join('.', [$type, $field]);

        yield $resolverServiceId => $resolverDefinition;
    }
}
