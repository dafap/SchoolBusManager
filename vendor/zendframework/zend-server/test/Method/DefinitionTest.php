<?php
/**
 * @see       https://github.com/zendframework/zend-server for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-server/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Server\Method;

use PHPUnit\Framework\TestCase;
use Zend\Server\Method;
use Zend\Server\Exception\InvalidArgumentException;

/**
 * Test class for \Zend\Server\Method\Definition
 *
 * @group      Zend_Server
 */
class DefinitionTest extends TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $this->definition = new Method\Definition();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
    }

    public function testCallbackShouldBeNullByDefault()
    {
        $this->assertNull($this->definition->getCallback());
    }

    public function testSetCallbackShouldAcceptMethodCallback()
    {
        $callback = new Method\Callback();
        $this->definition->setCallback($callback);
        $test = $this->definition->getCallback();
        $this->assertSame($callback, $test);
    }

    public function testSetCallbackShouldAcceptArray()
    {
        $callback = [
            'type'     => 'function',
            'function' => 'foo',
        ];
        $this->definition->setCallback($callback);
        $test = $this->definition->getCallback()->toArray();
        $this->assertSame($callback, $test);
    }

    public function testMethodHelpShouldBeEmptyStringByDefault()
    {
        $this->assertEquals('', $this->definition->getMethodHelp());
    }

    public function testMethodHelpShouldBeMutable()
    {
        $this->assertEquals('', $this->definition->getMethodHelp());
        $this->definition->setMethodHelp('foo bar');
        $this->assertEquals('foo bar', $this->definition->getMethodHelp());
    }

    public function testNameShouldBeNullByDefault()
    {
        $this->assertNull($this->definition->getName());
    }

    public function testNameShouldBeMutable()
    {
        $this->assertNull($this->definition->getName());
        $this->definition->setName('foo.bar');
        $this->assertEquals('foo.bar', $this->definition->getName());
    }

    public function testObjectShouldBeNullByDefault()
    {
        $this->assertNull($this->definition->getObject());
    }

    public function testObjectShouldBeMutable()
    {
        $this->assertNull($this->definition->getObject());
        $object = new \stdClass;
        $this->definition->setObject($object);
        $this->assertEquals($object, $this->definition->getObject());
    }

    public function testSettingObjectToNonObjectShouldThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid object passed to');
        $this->definition->setObject('foo');
    }

    public function testInvokeArgumentsShouldBeEmptyArrayByDefault()
    {
        $args = $this->definition->getInvokeArguments();
        $this->assertInternalType('array', $args);
        $this->assertEmpty($args);
    }

    public function testInvokeArgumentsShouldBeMutable()
    {
        $this->testInvokeArgumentsShouldBeEmptyArrayByDefault();
        $args = ['foo', ['bar', 'baz'], new \stdClass];
        $this->definition->setInvokeArguments($args);
        $this->assertSame($args, $this->definition->getInvokeArguments());
    }

    public function testPrototypesShouldBeEmptyArrayByDefault()
    {
        $prototypes = $this->definition->getPrototypes();
        $this->assertInternalType('array', $prototypes);
        $this->assertEmpty($prototypes);
    }

    public function testDefinitionShouldAllowAddingSinglePrototypes()
    {
        $this->testPrototypesShouldBeEmptyArrayByDefault();
        $prototype1 = new Method\Prototype;
        $this->definition->addPrototype($prototype1);
        $test = $this->definition->getPrototypes();
        $this->assertSame($prototype1, $test[0]);

        $prototype2 = new Method\Prototype;
        $this->definition->addPrototype($prototype2);
        $test = $this->definition->getPrototypes();
        $this->assertSame($prototype1, $test[0]);
        $this->assertSame($prototype2, $test[1]);
    }

    public function testDefinitionShouldAllowAddingMultiplePrototypes()
    {
        $prototype1 = new Method\Prototype;
        $prototype2 = new Method\Prototype;
        $prototypes = [$prototype1, $prototype2];
        $this->definition->addPrototypes($prototypes);
        $this->assertSame($prototypes, $this->definition->getPrototypes());
    }

    public function testSetPrototypesShouldOverwriteExistingPrototypes()
    {
        $this->testDefinitionShouldAllowAddingMultiplePrototypes();

        $prototype1 = new Method\Prototype;
        $prototype2 = new Method\Prototype;
        $prototypes = [$prototype1, $prototype2];
        $this->assertNotSame($prototypes, $this->definition->getPrototypes());
        $this->definition->setPrototypes($prototypes);
        $this->assertSame($prototypes, $this->definition->getPrototypes());
    }

    public function testDefintionShouldSerializeToArray()
    {
        $name       = 'foo.bar';
        $callback   = ['function' => 'foo', 'type' => 'function'];
        $prototypes = [['returnType' => 'struct', 'parameters' => ['string', 'array']]];
        $methodHelp = 'foo bar';
        $object     = new \stdClass;
        $invokeArgs = ['foo', ['bar', 'baz']];
        $this->definition->setName($name)
                         ->setCallback($callback)
                         ->setPrototypes($prototypes)
                         ->setMethodHelp($methodHelp)
                         ->setObject($object)
                         ->setInvokeArguments($invokeArgs);
        $test = $this->definition->toArray();
        $this->assertEquals($name, $test['name']);
        $this->assertEquals($callback, $test['callback']);
        $this->assertEquals($prototypes, $test['prototypes']);
        $this->assertEquals($methodHelp, $test['methodHelp']);
        $this->assertEquals($object, $test['object']);
        $this->assertEquals($invokeArgs, $test['invokeArguments']);
    }

    public function testPassingOptionsToConstructorShouldSetObjectState()
    {
        $options = [
            'name'            => 'foo.bar',
            'callback'        => ['function' => 'foo', 'type' => 'function'],
            'prototypes'      => [['returnType' => 'struct', 'parameters' => ['string', 'array']]],
            'methodHelp'      => 'foo bar',
            'object'          => new \stdClass,
            'invokeArguments' => ['foo', ['bar', 'baz']],
        ];
        $definition = new Method\Definition($options);
        $test = $definition->toArray();
        $this->assertEquals($options['name'], $test['name']);
        $this->assertEquals($options['callback'], $test['callback']);
        $this->assertEquals($options['prototypes'], $test['prototypes']);
        $this->assertEquals($options['methodHelp'], $test['methodHelp']);
        $this->assertEquals($options['object'], $test['object']);
        $this->assertEquals($options['invokeArguments'], $test['invokeArguments']);
    }
}
