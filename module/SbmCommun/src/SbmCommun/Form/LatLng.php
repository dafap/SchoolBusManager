<?php
/**
 * Formulaire permettant de saisir et de valider des coordonnées
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Form
 * @filesource LatLng.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 juin 2015
 * @version 2015-1
 */
namespace SbmCommun\Form;

use Zend\InputFilter\InputFilterProviderInterface;

class LatLng extends ButtonForm implements InputFilterProviderInterface
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

    /**
     * Constructeur d'un ButtonForm avec les paramètres de validation de lat et lng
     *
     * @param array $hiddens
     *            tableau des éléments inputs de type hidden, autres que lat et lng servant notamment à passer les id
     * @param array $submits
     *            tableau décrivant les boutons
     * @param array $valide
     *            tableau dont les clés 'lat' et 'lng' sont associées à un tableau à 2 réels array(min, max)
     */
    public function __construct(array $hiddens, array $submits, array $valide)
    {
        $ok = array_key_exists('lat', $valide);
        $ok &= array_key_exists('lng', $valide);
        if ($ok) {
            $ok &= count($valide['lat']) == 2;
            $ok &= count($valide['lng']) == 2;
            foreach ($valide['lat'] as $item) {
                $ok &= is_numeric($item);
            }
            foreach ($valide['lng'] as $item) {
                $ok &= is_numeric($item);
            }
        }
        if (! $ok) {
            ob_start();
            var_dump($valide);
            throw new Exception(__METHOD__ . " - Le paramètre \"valide\" est incorrect.\n" . ob_get_clean());
        }
        $this->latRange = $valide['lat'];
        $this->lngRange = $valide['lng'];
        $hiddens['lat'] = array(
            'id' => 'lat',
            'options' => array(
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        );
        $hiddens['lng'] = array(
            'id' => 'lng',
            'options' => array(
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        );
        parent::__construct($hiddens, $submits);
    }

    public function getInputFilterSpecification()
    {
        return array(
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
            )
        );
    }

    public function isValid()
    {
        $valid = parent::isValid();
        if ($valid)
            return true;
            // il y a une erreur sur lat ou lng
        $lat = $this->get('lat');
        $lat->setMessages(array(
            'Le lieu indiqué n\'est pas dans la zone géographique de l\'organisateur.'
        ));
        $lng = $this->get('lng');
        $lng->setMessages(array(
            'Utilisez le zoom ou la molette de la souris pour mieux voir sur la carte.'
        ));
        return false;
    }
}