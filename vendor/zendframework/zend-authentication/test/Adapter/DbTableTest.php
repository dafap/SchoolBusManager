<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Authentication\Adapter;

use Zend\Authentication\Adapter;

/**
 * @group      Zend_Auth
 * @group      Zend_Db_Table
 */
class DbTableTest extends DbTable\CredentialTreatmentAdapterTest
{
    // @codingStandardsIgnoreStart
    protected function _setupAuthAdapter()
    {
        // @codingStandardsIgnoreEnd
        $this->_adapter = new Adapter\DbTable($this->_db, 'users', 'username', 'password');
    }
}
