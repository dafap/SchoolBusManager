<?php
/**
 * Adapter pour paginator spécial pour MailChimp
 *
 * Cet adapter interroge MailChimp pour charger la page demandée.
 * Il donne aussi le nombre de réponses attendues.
 * 
 * @project sbm
 * @package SbmMailChimp/Model/Paginator/Adapter
 * @filesource MailChimpAdapter.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmMailChimp\Model\Paginator\Adapter;

use Zend\Paginator\Adapter\AdapterInterface;
use DrewM\MailChimp\MailChimp;
use SbmBase\Model\StdLib;

class MailChimpAdapter implements AdapterInterface
{

    /**
     *
     * @var MailChimp
     */
    protected $mailchimp;

    /**
     *
     * @var string
     */
    protected $method;

    /**
     * Nom du container (lists, segments, members, merge_fields .
     *
     * ..)
     *
     * @var string
     */
    protected $container;

    /**
     *
     * @var int
     */
    protected $count;

    public function __construct(MailChimp $mailchimp, $method, $container)
    {
        $this->mailchimp = $mailchimp;
        $this->method = $method;
        $this->container = $container;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\Paginator\Adapter\AdapterInterface::getItems()
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $method = sprintf('%s?offset=%d&count=%d', $this->method, $offset, 
            $itemCountPerPage);
        return $this->mailchimp->get($method)[$this->container];
    }

    /**
     * On lance la requête `get` sans préciser `offset` et `limit` car par défaut, offset = 0 et limit = 10.
     * Dans le résultat, la clé `total_items` contient l'effectif total.
     *
     * (non-PHPdoc)
     *
     * @see Countable::count()
     */
    public function count()
    {
        if ($this->count === null) {
            $this->count = StdLib::getParam('total_items', 
                $this->mailchimp->get($this->method));
        }
        return $this->count;
    }
}