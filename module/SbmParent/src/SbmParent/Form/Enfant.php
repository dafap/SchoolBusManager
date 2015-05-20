<?php
/**
 * Formulaire d'inscription pour un parent
 *
 * Ce formulaire ne présente pas de garde alternée.
 * Lorsqu'il s'agit d'un nouvel élève, il est forcément inscrit par son responsable1.
 * 
 * @project sbm
 * @package SbmParent/Form
 * @filesource Enfant.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 avr. 2015
 * @version 2015-1
 */
namespace SbmParent\Form;

use Zend\InputFilter\InputFilterProviderInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Where;
use SbmCommun\Form\AbstractSbmForm;
use SbmCommun\Filter\SansAccent;
use SbmCommun\Model\Strategy\Semaine;

class Enfant extends AbstractSbmForm implements InputFilterProviderInterface
{

    /**
     * Service manager (nécessaire pour vérifier l'email)
     *
     * @var ServiceLocatorInterface
     */
    private $sm;

    public function __construct(ServiceLocatorInterface $sm)
    {
        $this->sm = $sm;
        parent::__construct('enfant');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'csrf',
            'type' => 'Zend\Form\Element\Csrf',
            'options' => array(
                'csrf_options' => array(
                    'timeout' => 180
                )
            )
        ));
        $this->add(array(
            'type' => 'hidden',
            'name' => 'eleveId'
        ));
        $this->add(array(
            'type' => 'hidden',
            'name' => 'responsable1Id'
        ));
        $this->add(array(
            'type' => 'hidden',
            'name' => 'responsable2Id'
        ));
        $this->add(array(
            'type' => 'SbmCommun\Form\Element\NomPropre',
            'name' => 'nom',
            'attributes' => array(
                'id' => 'enfant_nom',
                'autofocus' => 'autofocus',
                'class' => 'sbmparent-enfant'
            ),
            'options' => array(
                'label' => 'Nom',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'SbmCommun\Form\Element\Prenom',
            'name' => 'prenom',
            'attributes' => array(
                'id' => 'enfant_prenom',
                'class' => 'sbmparent-enfant'
            ),
            'options' => array(
                'label' => 'Prénom',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Date',
            'name' => 'dateN',
            'attributes' => array(
                'id' => 'enfant_dateN',
                'class' => 'sbmparent-enfant'
            ),
            'options' => array(
                'label' => 'Date de naissance',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                ),
                'format' => 'Y-m-d'
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'etablissementId',
            'attributes' => array(
                'id' => 'enfant_etablissementId',
                'class' => 'sbmparent-enfant'
            ),
            'options' => array(
                'label' => 'Etablissement scolaire',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'empty_option' => 'Etablissement fréquenté l\'année prochaine',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'classeId',
            'attributes' => array(
                'id' => 'enfant_classeId',
                'class' => 'sbmparent-enfant'
            ),
            'options' => array(
                'label' => 'Classe suivie l\'année prochaine',
                'label_attributes' => array(
                    'class' => 'sbm-label'
                ),
                'empty_option' => 'Choisissez une classe',
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\MultiCheckbox',
            'name' => 'joursTransport',
            'attributes' => array(
                'id' => 'enfant_joursTransport',
                'class' => 'sbmparent-enfant'
            ),
            'options' => array(
                'label' => 'Demande de transport',
                'label_attributes' => array(
                    'class' => 'sbm-multi-label'
                ),
                'error_attributes' => array(
                    'class' => 'sbm-error'
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'ga',
            'attributes' => array(
                'id' => 'btnradioga',
                'class' => 'sbmparent-enfant',
                'value' => '0'
            ),
            'options' => array(
                'label' => 'Garde alternée',
                'label_attributes' => array(
                    'class' => 'sbm-radio-label'
                ),
                'value_options' => array(
                    array(
                        'value' => '1',
                        'label' => 'Oui',
                        'attributes' => array('id' =>'btnradioga1') 
                    ),
                    array(
                        'value' => '0',
                        'label' => 'Non',
                        'attributes' => array('id' => 'btnradioga0')
                    )
                )
            )
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'demandeR2',
            'attributes' => array(
                'id' => 'demandeR2',
                'class' => 'sbmparent-enfant',
                'value' => 0
            ),
            'options' => array(
                'label' => 'Demande de transport pour cette adresse',
                'label_attributes' => array(
                    'class' => 'sbm-radio-label'
                ),
                'value_options' => array(
                    '1' => 'Oui',
                    '0' => 'Non'
                )
            )
        ));
        $this->add(array(
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => array(
                'value' => 'Enregistrer',
                'id' => 'enfant_submit',
                'class' => 'button default submit'
            )
        ));
        $this->add(array(
            'type' => 'submit',
            'name' => 'cancel',
            'attributes' => array(
                'value' => 'Abandonner',
                'id' => 'enfant_cancel',
                'class' => 'button default cancel'
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            
            'joursTransport' => array(
                'name' => 'joursTransport',
                'required' => true
            ),
            'demandeR2' => array(
                'name' => 'demandeR2',
                'required' => false
            )
        );
    }

    public function isValid()
    {
        if (parent::isValid()) {
            $data = $this->getData();
            // vérifie que la classe est ouverte dans l'établissement
            $classe = $this->sm->get('Sbm\Db\Table\Classes')->getRecord($data['classeId']);
            $etablissement = $this->sm->get('Sbm\Db\Table\Etablissements')->getRecord($data['etablissementId']);
            $ok = false;
            foreach ($classe->niveau as $n) {
                $ok |= in_array($n, $etablissement->niveau);
            }
            if (! $ok) {
                $this->setMessages(array(
                    'classeId' => array(
                        'incorrect' => 'Cette classe n\'est pas ouverte dans cet établissement.'
                    )
                ));
            }
            // vérifie que l'élève n'est pas inscrit 
            if (empty($data['eleveId'])) {
                // lorsqu'il s'agit d'un nouvel élève
                $sa = new SansAccent();
                $where = new Where();
                $where->equalTo('nomSA', $sa->filter($data['nom']))
                    ->equalTo('prenomSA', $sa->filter($data['prenom']))
                    ->equalTo('dateN', $data['dateN'])
                    ->nest()
                    ->equalTo('responsable1Id', $data['responsable1Id'])->OR->equalTo('responsable2Id', $data['responsable1Id'])->unnest();
                $ok = $this->sm->get('Sbm\Db\Table\Eleves')
                    ->fetchAll($where)
                    ->count() == 0;
                if (! $ok) {
                    $this->setMessages(array(
                        'prenom' => array(
                            'existe' => 'Cet enfant est déjà enregistré.'
                        )
                    ));
                }
            } else {
                // lorsqu'il s'agit d'une modification
                $sa = new SansAccent();
                $where = new Where();
                $where->equalTo('nomSA', $sa->filter($data['nom']))
                ->equalTo('prenomSA', $sa->filter($data['prenom']))
                ->equalTo('dateN', $data['dateN'])
                ->notEqualTo('eleveId', $data['eleveId'])
                ->nest()
                ->equalTo('responsable1Id', $data['responsable1Id'])->OR->equalTo('responsable2Id', $data['responsable2Id'])->unnest();
                $ok = $this->sm->get('Sbm\Db\Table\Eleves')
                ->fetchAll($where)
                ->count() == 0;
                if (! $ok) {
                    $this->setMessages(array(
                        'prenom' => array(
                            'existe' => 'Cet enfant est déjà enregistré.'
                        )
                    ));
                }
            }
            return $ok;
        } else {
            return false;
        }
    }
    
    /**
     * Traitement de l'élément 'joursTransport' dans les données reçues avant de charger le formulaire
     * 
     * (non-PHPdoc)
     * @see \Zend\Form\Form::setData()
     */
    public function setData($data)
    {
        if (is_array($data) && array_key_exists('joursTransport', $data) && !is_array($data['joursTransport'])) {
            $strategie = new Semaine();
            $data['joursTransport'] = $strategie->hydrate($data['joursTransport']);
        }
        return parent::setData($data);
    }
}