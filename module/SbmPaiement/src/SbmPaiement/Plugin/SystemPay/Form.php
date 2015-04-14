<?php
/**
 * Formulaire pour un appel à la plateforme Systempay
 *
 * Le formulaire conforme au `Guide_d'implementation_formulaire_paiement30012015081841.pdf`
 * - Guide d'implementation du formulaire de paiement Systempay 2.2 - Version du document 3.0
 * est remplacé par un appel CURL
 * 
 * @project sbm
 * @package SbmPaiement/Plugin/SystemPay/Form
 * @filesource Form.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 mars 2015
 * @version 2015-1
 */
namespace SbmPaiement\Plugin\SystemPay;

use Zend\Form\Form as ZendForm;

class Form extends ZendForm
{

    /**
     * Liste des champs hidden du formulaire
     * 
     * @var array
     */
    private $vads;

    public function __construct($param = 'spplus')
    {
        $this->vads = include 'config/vads.inc.php';
        parent::__construct($param);
        $this->setAttribute('method', 'post');
        $this->setAttribute('action', $config['url_paiement']);
        
        foreach ($vads as $item) {
            $this->add(array(
                'name' => $item,
                'type' => 'hidden'
            ));
        }
        
        $this->add(array(
            'name' => 'signature',
            'type' => 'hidden'
        ));
        
        $this->add(array(
            'name' => 'payer',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Payer',
                'id' => 'payer',
                'autofocus' => 'autofocus',
                'class' => 'button default submit'
            )
        ));
    }

    /**
     * Surcharge la méthode en calculant la signature
     * Toutes les données du formulaire viennent d'un tableau associatif dont l'une des clés est `certificat`
     * @param array $data
     */
    private function setData($data)
    {
        ksort($data);
        $signature = '';
        foreach ($data as $key => $value) {
            if (substr($key, 0, 5) == 'vads_') {
                $signature .= "$value+";
            }
        }
        $signature .= $data['certificat'];
        $data['signature'] = sha1($signature);
        parent::setData($data);
    }
    
    /**
     * Renvoie le tableau des champs vads_
     * 
     * @return array
     */
    public function getVads()
    {
        return $this->vads;
    }
}