<?php
/**
 * @see       https://github.com/zendframework/zend-server for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-server/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Server;

use PHPUnit\Framework\TestCase;
use Zend\Server;
use Zend\Server\Method;

/**
 * Test class for Zend\Server\Definition
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
        $this->definition = new Server\Definition();
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

    public function testMethodsShouldBeEmptyArrayByDefault()
    {
        $methods = $this->definition->getMethods();
        $this->assertInternalType('array', $methods);
        $this->assertEmpty($methods);
    }

    public function testDefinitionShouldAllowAddingSingleMethods()
    {
        $method = new Method\Definition(['name' => 'foo']);
        $this->definition->addMethod($method);
        $methods = $this->definition->getMethods();
        $this->assertCount(1, $methods);
        $this->assertSame($method, $methods['foo']);
        $this->assertSame($method, $this->definition->getMethod('foo'));
    }

    public function testDefinitionShouldAllowAddingMultipleMethods()
    {
        $method1 = new Method\Definition(['name' => 'foo']);
        $method2 = new Method\Definition(['name' => 'bar']);
        $this->definition->addMethods([$method1, $method2]);
        $methods = $this->definition->getMethods();
        $this->assertCount(2, $methods);
        $this->assertSame($method1, $methods['foo']);
        $this->assertSame($method1, $this->definition->getMethod('foo'));
        $this->assertSame($method2, $methods['bar']);
        $this->assertSame($method2, $this->definition->getMethod('bar'));
    }

    public function testSetMethodsShouldOverwriteExistingMethods()
    {
        $this->testDefinitionShouldAllowAddingMultipleMethods();
        $method1 = new Method\Definition(['name' => 'foo']);
        $method2 = new Method\Definition(['name' => 'bar']);
        $methods = [$method1, $method2];
        $this->assertNotEquals($methods, $this->definition->getMethods());
        $this->definition->setMethods($methods);
        $test = $this->definition->getMethods();
        $this->assertEquals(array_values($methods), array_values($test));
    }

    public function testHasMethodShouldReturnFalseWhenMethodNotRegisteredWithDefinition()
    {
        $this->assertFalse($this->definition->hasMethod('foo'));
    }

    public function testHasMethodShouldReturnTrueWhenMethodRegisteredWithDefinition()
    {
        $this->testDefinitionShouldAllowAddingMultipleMethods();
        $this->assertTrue($this->definition->hasMethod('foo'));
    }

    public function testDefinitionShouldAllowRemovingIndividualMethods()
    {
        $this->testDefinitionShouldAllowAddingMultipleMethods();
        $this->assertTrue($this->definition->hasMethod('foo'));
        $this->definition->removeMethod('foo');
        $this->assertFalse($this->definition->hasMethod('foo'));
    }

    public function testDefinitionShouldAllowClearingAllMethods()
    {
        $this->testDefinitionShouldAllowAddingMultipleMethods();
        $this->definition->clearMethods();
        $test = $this->definition->getMethods();
        $this->assertEmpty($test);
    }

    public function testDefinitionShouldSerializeToArray()
    {
        $method = [
            'name' => 'foo.bar',
            'callback' => [
                'type'     => 'function',
                'function' => 'bar',
            ],
            'prototypes' => [
                [
                    'returnType' => 'string',
                    'parameters' => ['string'],
                ],
            ],
            'methodHelp' => 'Foo Bar!',
            'invokeArguments' => ['foo'],
        ];
        $definition = new Server\Definition();
        $definition->addMethod($method);
        $test = $definition->toArray();
        $this->assertCount(1, $test);
        $test = array_shift($test);
        $this->assertEquals($method['name'], $test['name']);
        $this->assertEquals($method['methodHelp'], $test['methodHelp']);
        $this->assertEquals($method['invokeArguments'], $test['invokeArguments']);
        $this->assertEquals($method['prototypes'][0]['returnType'], $test['prototypes'][0]['returnType']);
    }

    public function testPassingOptionsToConstructorShouldSetObjectState()
    {
        $method = [
            'name' => 'foo.bar',
            'callback' => [
                'type'     => 'function',
                'function' => 'bar',
            ],
            'prototypes' => [
                [
                    'returnType' => 'string',
                    'parameters' => ['string'],
                ],
            ],
            'methodHelp' => 'Foo Bar!',
            'invokeArguments' => ['foo'],
        ];
        $options = [$method];
        $definition = new Server\Definition($options);
        $test = $definition->toArray();
        $this->assertCount(1, $test);
        $test = array_shift($test);
        $this->assertEquals($method['name'], $test['name']);
        $this->assertEquals($method['methodHelp'], $test['methodHelp']);
        $this->assertEquals($method['invokeArguments'], $test['invokeArguments']);
        $this->assertEquals($method['prototypes'][0]['returnType'], $test['prototypes'][0]['returnType']);
    }
}
