<?php
/**
 * Formulaire pour un appel à la plateforme
 *
 *
 * @project sbm
 * @package SbmPaiement/Plugin/PayFiP
 * @filesource Form.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmPaiement\Plugin\PayFiP;

use Zend\Form\Form as ZendForm;

class Form extends ZendForm
{
    public function __construct($param = 'payfip', $url_paiement)
    {
        parent::__construct($param);
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', $url_paiement);
    }
}
