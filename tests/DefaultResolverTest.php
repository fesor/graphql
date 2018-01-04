<?php

namespace Tests\Fesor\GraphQL;

use Fesor\GraphQL\DefaultResolver;
use GraphQL\Type\Definition\ResolveInfo;
use PHPUnit\Framework\TestCase;

class DefaultResolverTest extends TestCase
{
    private $resolver;

    public function setUp()
    {
        $this->resolver = new DefaultResolver();
    }

    /**
     * @dataProvider sourceProvider
     * @param mixed $source
     */
    public function testAccessors($source)
    {
        $resolveInfo = new ResolveInfo([
            'fieldName' => 'foo'
        ]);
        $args = [
            'bar' => 'bar'
        ];

        $this->assertEquals('bar', ($this->resolver)($source, $args, 'context', $resolveInfo));
    }

    public function sourceProvider()
    {
        yield [['foo' => 'bar']];

        yield [new \ArrayObject(['foo' => 'bar'])];

        yield [(object) ['foo' => 'bar']];

        yield [new class {
            public function getFoo() {
                return 'bar';
            }
        }];

        yield [new class {
            public function foo() {
                return 'bar';
            }
        }];

        yield [new class {
            public function foo($args) {
                ['bar' => $bar] = $args;

                return $bar;
            }
        }];
    }
}
