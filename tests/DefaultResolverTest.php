<?php

namespace Tests\Fesor\GraphQL;

use Fesor\GraphQL\DefaultResolver;
use Fesor\GraphQL\Exception\UnableToResolveArgument;
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
     * @dataProvider sourceWithDifferentAccessorsProvider
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

        $this->assertEquals('bar', ($this->resolver)($source, $args, $resolveInfo));
    }

    public function sourceWithDifferentAccessorsProvider()
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
            public function foo(string $bar) {
                return $bar;
            }
        }];
    }

    /**
     * @dataProvider sourceWithDifferentSignaturesProvider
     * @param $source
     */
    public function testItBindsArgumentsOfAccessors($source)
    {
        $resolveInfo = new ResolveInfo([
            'fieldName' => 'foo'
        ]);
        $args = [
            'foo' => 'foo',
            'bar' => 'bar',
        ];
        $this->assertEquals('foo', ($this->resolver)($source, $args, $resolveInfo));
    }

    public function sourceWithDifferentSignaturesProvider()
    {
        yield [new class {
            public function foo($foo, $bar) {
                return empty($bar) ? $bar : $foo;
            }
        }];
        yield [new class {
            public function foo(string $foo, string $bar) {
                return empty($bar) ? $bar : $foo;
            }
        }];
        yield [new class {
            public function foo(ResolveInfo $info, string $foo, string $bar) {
                return empty($bar) ? $bar : $foo;
            }
        }];
        yield [new class {
            public function foo(string $foo, ResolveInfo $info, string $bar) {
                return empty($bar) ? $bar : $foo;
            }
        }];
        yield [new class {
            public function foo(ResolveInfo $info, string $foo) {
                return $foo;
            }
        }];
        yield [new class {
            public function foo(ResolveInfo $info, string $foo, int $baz = null) {
                return $foo;
            }
        }];
    }

    public function testThrowsExceptionInCaseIfNoValueCanBeProvidedForArgument()
    {
        $source = new class {
            public function foo(ResolveInfo $info, string $foo, int $baz) {
                return $foo;
            }
        };
        $resolveInfo = new ResolveInfo([
            'fieldName' => 'foo'
        ]);
        $args = [
            'foo' => 'foo',
            'bar' => 'bar',
        ];

        $this->expectException(UnableToResolveArgument::class);
        $this->expectExceptionMessage("Unable to resolve value for 'baz'");
        ($this->resolver)($source, $args, $resolveInfo);
    }
}
