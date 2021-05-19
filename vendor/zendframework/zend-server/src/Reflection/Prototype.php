<?php
/**
 * @see       https://github.com/zendframework/zend-server for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-server/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Server\Reflection;

/**
 * Method/Function prototypes
 *
 * Contains accessors for the return value and all method arguments.
 */
class Prototype
{
    /** @var ReflectionParameter[] */
    protected $params;

    /**
     * Constructor
     *
     * @param ReflectionReturnValue $return
     * @param ReflectionParameter[] $params
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(ReflectionReturnValue $return, array $params = [])
    {
        $this->return = $return;

        foreach ($params as $param) {
            if (! $param instanceof ReflectionParameter) {
                throw new Exception\InvalidArgumentException('One or more params are invalid');
            }
        }

        $this->params = $params;
    }

    /**
     * Retrieve return type
     *
     * @return string
     */
    public function getReturnType()
    {
        return $this->return->getType();
    }

    /**
     * Retrieve the return value object
     *
     * @return \Zend\Server\Reflection\ReflectionReturnValue
     */
    public function getReturnValue()
    {
        return $this->return;
    }

    /**
     * Retrieve method parameters
     *
     * @return ReflectionParameter[] Array of {@link \Zend\Server\Reflection\ReflectionParameter}s
     */
    public function getParameters()
    {
        return $this->params;
    }
}
