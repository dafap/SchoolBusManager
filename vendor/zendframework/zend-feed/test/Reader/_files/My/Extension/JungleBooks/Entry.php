<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace My\Extension\JungleBooks;

use Zend\Feed\Reader\Extension;

class Entry extends Extension\AbstractEntry
{

    public function getIsbn()
    {
        if (isset($this->data['isbn'])) {
            return $this->data['isbn'];
        }
        $isbn = $this->xpath->evaluate('string(' . $this->getXpathPrefix() . '/jungle:isbn)');
        if (! $isbn) {
            $isbn = null;
        }
        $this->data['isbn'] = $title;
        return $this->data['isbn'];
    }

    protected function registerNamespaces()
    {
        $this->xpath->registerNamespace('jungle', 'http://example.com/junglebooks/rss/module/1.0/');
    }
}
