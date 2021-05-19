<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Authentication\Adapter\Http\TestAsset;

use Zend\Authentication\Result as AuthenticationResult;
use Zend\Authentication\Adapter\Http\ResolverInterface;

class BasicAuthObjectResolver implements ResolverInterface
{
    public function resolve($username, $realm, $password = null)
    {
        if ($username == 'Bryce' && $password == 'ThisIsNotMyPassword') {
            $identity = new \stdClass();

            return new AuthenticationResult(
                AuthenticationResult::SUCCESS,
                $identity,
                ['Authentication successful.']
            );
        }

        return new AuthenticationResult(
            AuthenticationResult::FAILURE,
            null,
            ['Authentication failed.']
        );
    }
}
