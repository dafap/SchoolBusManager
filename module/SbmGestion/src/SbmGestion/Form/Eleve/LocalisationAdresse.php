<?php
/**
 * Formulaire permettant de saisir l'adresse particulière d'un élève et de la géolocaliser
 *
 * Les champs du formulaire sont :
 * 
 * @project sbm
 * @package SbmGestion/Fomr/Eleve
 * @filesource LocalisationAdresse.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 juin 2015
 * @version 2015-1
 */
namespace SbmGestion\Form\Eleve;

use SbmCommun\Form\AbstractSbmForm;
use Zend\InputFilter\InputFilterProviderInterface;

class LocalisationAdresse extends AbstractSbmForm implements InputFilterProviderInterface
{

    /**
     * Dans un tableau simple, minimum et maximum autorisés pour la latitude
     *
     * @var array
     */
    private $latRange;
    
    /**
     * Dans un tableau simple, minimum et maximum autorisés pour la longitude
     *
     * @var array
     */
    private $lngRange;
    
    public function __construct(array $valide, $name = 'chez', $options = array())
    {
        $ok = array_key_exists('lat', $valide);
        $ok &= array_key_exists('lng', $valide);
        $ok &= count($valide['lat']) == 2;
        $ok &= count($valide['lng']) == 2;
        foreach ($valide['lat'] as $item) {
            $ok &= is_numeric($item);
        }
        foreach ($valide['lng'] as $item) {
            $ok &= is_numeric($item);
        }
        if (! $ok) {
            throw new Exception(__METHOD__ . ' - Le paramètre "valide" est incorrect.');
        }
        $this->latRange = $valide['lat'];
        $this->lngRange = $valide['lng'];
        
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');
        $this->add(array(
            'type' => 'hidden',
            'name' => 'eleveId',
            'attributes' => array(
                'id' => 'eleveId'
            )
        ));
        $this->add(array(
            'type' => 'hidden',
            'name' => 'lat',
            'attributes' => array(
                'id' => 'eleve-lat'
            )
        ));
        $this->add(array(
            'type' => 'hidden',
            'name' => 'lng',
            'attributes' => array(
                'id' => 'eleve-lng'
            )
        ));
        $this->add(array(
            'type' => 'SbmCommun\Form\Element\NomPropre',
            'name' => 'chez',
            'attributes' => array(
                'id' => 'eleve-chez',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Chez',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'SbmCommun\Form\Element\Adresse',
            'name' => 'adresseL1',
            'attributes' => array(
                'id' => 'eleve-adresseL1',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Adresse',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'SbmCommun\Form\Element\Adresse',
            'name' => 'adresseL2',
            'attributes' => array(
                'id' => 'eleve-addresseL2',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Complément d\'adresse',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'SbmCommun\Form\Element\CodePostal',
            'name' => 'codePostal',
            'attributes' => array(
                'id' => 'eleve-codePostal',
                'class' => 'sbm-width-5c'
            ),
            'options' => array(
                'label' => 'Code postal',
                'label_attributes' => array(
                    'class' => 'sbm-label new-line'
                ),
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'communeId',
            'attributes' => array(
                'id' => 'eleve-communeId',
                'class' => 'sbm-width-30c'
            ),
            'options' => array(
                'label' => 'Commune',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'empty_option' => 'Choisissez une commune',
                'error_options' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => array(
                'class' => 'button default submit left-95px',
                'value' => 'Enregistrer'
            )
        ));
        $this->add(array(
            'type' => 'submit',
            'name' => 'remove',
            'attributes' => array(
                'class' => 'button default submit left-10px',
                'value' => 'Supprimer'
            )
        ));
        $this->add(array(
            'type' => 'submit',
            'name' => 'cancel',
            'attributes' => array(
                'class' => 'button default cancel left-10px',
                'value' => 'Abandonner'
            )
        ));
    }
    
    public function getInputFilterSpecification()
    {
        return array(
            'eleveId' => array(
                'name' => 'eleveId',
                'required' => true
            ),
            'lat' => array(
                'name' => 'lat',
                'required' => true,
                'validators' => array(
                    new \Zend\Validator\Between(array(
                        'min' => $this->latRange[0],
                        'max' => $this->latRange[1]
                    ))
                )
            ),
            'lng' => array(
                'name' => 'lng',
                'required' => true,
                'validators' => array(
                    new \Zend\Validator\Between(array(
                        'min' => $this->lngRange[0],
                        'max' => $this->lngRange[1]
                    ))
                )
            ),
            'adresseL1' => array(
                'name' => 'adresseL1',
                'required' => true
            ),
            'adresseL2' => array(
                'name' => 'adresseL2',
                'required' => false
            )
        );
    }
}