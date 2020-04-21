<?php
/**
 * Formulaire d'envoi
 *
 * Il a un id en cohérence avec celui utilisé dans le script jQuery de formulaire.phtml
 * contenu dans /public/js/tra/sbm-paiement/formulaire.js
 * Il n'est composé que d'éléments hidden dont le nom est référencés dans paybox.
 * La liste des champs est passée dans $options sous la forme d'un tableau indexé.
 * Elle se termine par 'PBX_RUF1' => 'POST' et 'PBX_HMAC'
 *
 * @project sbm
 * @package SbmPaiement/src/Plugin/PayBox
 * @filesource Formulaire.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmPaiement\Plugin\PayBox;

use Zend\Form\Form;
use SbmPaiement\Plugin\Exception;

class Formulaire extends Form
{

    /**
     *
     * @param string $name
     * @param array $options
     *            La clé 'hiddens' doit être présente et donne un tableau indexé des noms
     *            des champs du formulaire.
     * @throws Exception
     */
    public function __construct(string $name = null, $options = [])
    {
        if (! array_key_exists('hiddens', $options)) {
            throw new Exception(
                'Absence des paramètres d\'initialisation du formulaire de Paybox.');
        }
        parent::__construct($name, $options);
        // id nécessaire pour être en cohérence avec le script jQuery : formulaire.js
        $this->setAttribute('id', 'Form');
        // création des champs du formulaire, tous en hidden, sans bouton ni
        // input[type=submit]
        foreach ($this->options['hiddens'] as $name) {
            if ($name != 'PBX_HMAC' && $name != 'PBX_RUF1') {
                $this->add([
                    'name' => $name,
                    'type' => 'hidden'
                ]);
            }
        }
        // impose une notification en POST
        $this->add(
            [
                'name' => 'PBX_RUF1',
                'type' => 'hidden',
                'attributes' => [
                    'value' => 'POST'
                ]
            ]);
        // doit être le dernier contrôle
        $this->add([
            'name' => 'PBX_HMAC',
            'type' => 'hidden'
        ]);
    }
}