<?php
/**
 * Gestion des listes de diffusion de MailChimp
 *
 * Nécessite l'installation de la bibliothèque DrewM\MailChimp
 * 
 * @project sbm
 * @package SbmMailChimp/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 juin 2016
 * @version 2016-2.1.7
 */
namespace SbmMailChimp\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use DrewM\MailChimp\MailChimp;
use Zend\Http\Response;
use SbmCommun\Model\StdLib;
use SbmCommun\Form\ButtonForm;
use SbmMailChimp\Form;
use SbmMailChimp\Model\Db\Service\Users;
use Zend\Paginator\Paginator;
use SbmMailChimp\Model\Paginator\Adapter\MailChimpAdapter;

class IndexController extends AbstractActionController
{

    /**
     * Affiche la liste des listes de diffusion présentent dans MailChimp
     *
     * https://developer.mailchimp.com/documentation/mailchimp/reference/lists/
     *
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $mailchimp = new MailChimp($this->config['mailchimp_key']);
        $method = 'lists';
        $listes = new Paginator(new MailChimpAdapter($mailchimp, $method, 'lists'));
        if (! $listes->count()) {
            $message = 'Aucune liste n\'a encore été créée.<br>Il faut en créer une.';
        } else {
            $message = '';
        }
        return new ViewModel([
            'source' => $listes,
            'auth' => $this->config['authenticate']->by('email'),
            'acl' => $this->config['acl'],
            'message' => $message
        ]);
    }

    /**
     * Renvoie à la liste (action index) avec un message dans flashMessenger.
     * Par défaut, 'error' avec comme message 'action interdite
     *
     * @param string $mode
     *            les modes sont 'error', 'warning', 'success' ou 'info'
     * @param string $msg
     *            le message à placer
     *            
     * @return \Zend\Http\Response
     */
    private function retourListe($mode = 'error', $msg = 'Action interdite.', $action = 'index')
    {
        switch ($mode) {
            case 'error':
                $this->flashMessenger()->addErrorMessage($msg);
                break;
            case 'warning':
                $this->flashMessenger()->addWarningMessage($msg);
                break;
            case 'success':
                $this->flashMessenger()->addSuccessMessage($msg);
                break;
            case 'info':
            default:
                $this->flashMessenger()->addInfoMessage($msg);
                break;
                break;
        }
        $this->removeInSession('identifiant', $this->getSessionNamespace());
        return $this->redirect()->toRoute('sbmmailchimp', [
            'action' => $action
        ]);
    }

    /**
     * Gestion des listes
     *
     * Cette action n'est autorisée par les acl que pour le sadmin
     *
     * @see https://developer.mailchimp.com/documentation/mailchimp/reference/lists/
     *
     * @throws \Exception
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function creerListeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } else {
            $args = (array) $prg;
            if (array_key_exists('cancel', $args)) {
                return $this->retourListe('warning', 'La liste n\'a pas été crée.');
            }
        }
        $mailchimp = new MailChimp($this->config['mailchimp_key']);
        $form = new Form\Liste();
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $params = $form->getDataForApi3(false);
                $result = $mailchimp->post('lists', $params);
                if (! array_key_exists('id', $result)) {
                    ob_start();
                    print_r($result);
                    $msg = ob_get_clean();
                    throw new \Exception($msg);
                }
                return $this->retourListe('success', 'La liste a été crée.');
            }
        } else {
            $form->setData([
                'company' => StdLib::getParamR([
                    'client',
                    'name'
                ], $this->config),
                'address1' => StdLib::getParamR([
                    'client',
                    'adresse',
                    0
                ], $this->config),
                'address2' => StdLib::getParamR([
                    'client',
                    'adresse',
                    1
                ], $this->config),
                'city' => StdLib::getParamR([
                    'client',
                    'commune'
                ], $this->config),
                'zip' => StdLib::getParamR([
                    'client',
                    'code_postal'
                ], $this->config),
                'country' => 'FR',
                'phone' => StdLib::getParamR([
                    'client',
                    'telephone'
                ], $this->config),
                'from_name' => StdLib::getParamR([
                    'mail_config',
                    'destinataires',
                    0,
                    'name'
                ], $this->config),
                'from_email' => StdLib::getParamR([
                    'mail_config',
                    'destinataires',
                    0,
                    'email'
                ], $this->config),
                'language' => 'fr',
                'email_type_option' => true
            ]);
        }
        $view = new ViewModel([
            'h1_msg' => 'Paramétrage d\'une nouvelle liste de diffusion',
            'form' => $form->prepare()
        ]);
        $view->setTemplate('sbm-mail-chimp/index/edit-liste.phtml');
        return $view;
    }

    /**
     * Cette action n'est autorisée par les acl que pour le sadmin
     *
     * @throws \Exception
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function dupliquerListeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            return $this->retourListe();
        } else {
            if (array_key_exists('dupliquer', $prg)) {
                if (! array_key_exists('id_liste', $prg)) {
                    return $this->retourListe();
                }
            }
            if (array_key_exists('cancel', $prg)) {
                return $this->retourListe('warning', 'La liste n\'a pas été dupliquée.');
            }
            $args = $prg;
        }
        $mailchimp = new MailChimp($this->config['mailchimp_key']);
        $form = new Form\Liste();
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $params = $form->getDataForApi3(false);
                $result = $mailchimp->post('lists', $params);
                if (! array_key_exists('id', $result)) {
                    ob_start();
                    print_r($result);
                    $msg = ob_get_clean();
                    throw new \Exception($msg);
                }
                return $this->retourListe('success', 'La liste a été dupliquée.');
            }
        } else {
            $result = $mailchimp->get('lists/' . $args['id_liste']);
            $form->setDataFromApi3($result, false);
        }
        $view = new ViewModel([
            'h1_msg' => 'Paramétrage d\'une nouvelle liste de diffusion à partir des paramètres d\'une autre',
            'form' => $form->prepare()
        ]);
        $view->setTemplate('sbm-mail-chimp/index/edit-liste.phtml');
        return $view;
    }

    public function editListeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = [
                'id_liste' => $this->getFromSession('identifiant', false, $this->getSessionNamespace())
            ];
            if ($args === false) {
                return $this->retourListe();
            }
        } else {
            if (array_key_exists('edit', $prg)) {
                if (array_key_exists('id_liste', $prg)) {
                    $this->setToSession('identifiant', $prg['id_liste'], $this->getSessionNamespace());
                } else {
                    return $this->retourListe();
                }
            }
            if (array_key_exists('cancel', $prg)) {
                return $this->retourListe('warning', 'Pas de modification.');
            }
            $args = $prg;
        }
        $mailchimp = new MailChimp($this->config['mailchimp_key']);
        $form = new Form\Liste();
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $params = $form->getDataForApi3();
                $result = $mailchimp->patch('lists/' . $args['id_liste'], $params);
                if (! array_key_exists('id', $result)) {
                    ob_start();
                    print_r($result);
                    $msg = ob_get_clean();
                    throw new \Exception($msg);
                }
                return $this->retourListe('success', 'Modification enregistrée.');
            }
        } else {
            $result = $mailchimp->get('lists/' . $args['id_liste']);
            $form->setDataFromApi3($result);
        }
        return new ViewModel([
            'h1_msg' => 'Modification du paramétrage d\'une liste de diffusion',
            'form' => $form->prepare()
        ]);
    }

    /**
     * Cette action n'est autorisée par les acl que pour le sadmin
     *
     * @throws \Exception
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function supprListeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            return $this->retourListe();
        } else {
            if (array_key_exists('suppr', $prg)) {
                if (! array_key_exists('id_liste', $prg)) {
                    return $this->retourListe();
                }
            }
            if (array_key_exists('supprnon', $prg)) {
                return $this->retourListe('warning', 'Pas de suppression.');
            }
            $args = $prg;
        }
        $mailchimp = new MailChimp($this->config['mailchimp_key']);
        $form = new ButtonForm([
            'id_liste' => $args['id_liste']
        ], [
            'supproui' => [
                'class' => 'confirm',
                'value' => 'Confirmer'
            ],
            'supprnon' => [
                'class' => 'confirm',
                'value' => 'Abandonner'
            ]
        ]);
        if (array_key_exists('supproui', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $result = $mailchimp->delete('lists/' . $args['id_liste']);
                if (is_array($result)) {
                    ob_start();
                    print_r($result);
                    $msg = ob_get_clean();
                    throw new \Exception($msg);
                }
                return $this->retourListe('success', sprintf('La liste %s a été supprimée.', $args['id_liste']));
            }
        }
        $liste_info = $mailchimp->get('lists/' . $args['id_liste']);
        
        return new ViewModel([
            'liste_info' => $liste_info,
            'form' => $form->prepare()
        ]);
    }

    /**
     * Gestion des champs des liste
     *
     * @see https://developer.mailchimp.com/documentation/mailchimp/reference/lists/merge-fields/
     *
     * @throws \Exception
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function fieldsListeAction()
    {
        // controle d'entrée
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('identifiant', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->retourListe();
            }
        } else {
            if (array_key_exists('id_liste', $prg)) {
                if (array_key_exists('fields', $prg)) {
                    $this->setToSession('identifiant', [
                        'id_liste' => $prg['id_liste'],
                        'liste_name' => $prg['liste_name']
                    ], $this->getSessionNamespace());
                }
            } elseif (! array_key_exists('retour', $prg)) {
                return $this->retourListe();
            }
            $args = array_merge($this->getFromSession('identifiant', [], $this->getSessionNamespace()), $prg);
        }
        $mailchimp = new MailChimp($this->config['mailchimp_key']);
        // lecture des infos de la liste
        $liste_info = $mailchimp->get('lists/' . $args['id_liste']);
        if (! array_key_exists('id', $liste_info)) {
            ob_start();
            echo "La liste n'a pas été trouvée.\n";
            var_dump($liste_info);
            $message = ob_get_clean();
            throw new \Exception($message);
        }
        // lecture des champs
        $method = 'lists/' . $args['id_liste'] . '/merge-fields';
        /**
         * ******************************
         * attention, faire la différence entre l'opérateur 'merge-fields' et le
         * container dans la résultat 'merge_fields' !!!!
         */
        $source = new Paginator(new MailChimpAdapter($mailchimp, $method, 'merge_fields'));
        if (! $source->count()) {
            ob_start();
            echo "Les champs de la liste n'ont pas été lus.\n";
            var_dump($source);
            $message = ob_get_clean();
            throw new \Exception($message);
        }
        return new ViewModel([
            'liste_info' => $liste_info,
            'auth' => $this->config['authenticate']->by('email'),
            'acl' => $this->config['acl'],
            'source' => $source
        ]);
    }

    public function editFieldAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('identifiant', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->retourListe('error', 'Action interdite.', 'fields-liste');
            }
        } else {
            if (array_key_exists('edit', $prg)) {
                if (array_key_exists('id_liste', $prg) && array_key_exists('merge_id', $prg)) {
                    $this->setToSession('identifiant', [
                        'id_liste' => $prg['id_liste'],
                        'merge_id' => $prg['merge_id']
                    ], $this->getSessionNamespace());
                } else {
                    return $this->retourListe('error', 'Action interdite', 'fields-liste');
                }
            }
            if (array_key_exists('cancel', $prg)) {
                return $this->retourListe('warning', 'Pas de modification.', 'fields-liste');
            }
            $args = $prg;
        }
        $mailchimp = new MailChimp($this->config['mailchimp_key']);
        $form = new Form\Field();
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $params = $form->getDataForApi3();
                /**
                 * *******************************
                 * Attention à la syntaxe : opérateur 'merge-fields' et clés dans les
                 * paramètres ou le résultat 'merge_fields' et 'merge_id'
                 */
                $result = $mailchimp->patch('lists/' . $args['id_liste'] . '/merge-fields/' . $args['merge_id'], $params);
                if (! array_key_exists('merge_id', $result)) {
                    ob_start();
                    print_r($result);
                    print_r($params);
                    $msg = ob_get_clean();
                    throw new \Exception($msg);
                }
                return $this->retourListe('success', 'Modification enregistrée.', 'fields-liste');
            }
        } else {
            $result = $mailchimp->get('lists/' . $args['id_liste'] . '/merge-fields/' . $args['merge_id']);
            $form->setDataFromApi3($result);
        }
        // lecture des infos de la liste
        $liste_info = $mailchimp->get('lists/' . $args['id_liste']);
        if (! array_key_exists('id', $liste_info)) {
            ob_start();
            echo "La liste n'a pas été trouvée.\n";
            var_dump($liste_info);
            $message = ob_get_clean();
            throw new \Exception($message);
        }
        return new ViewModel([
            'h1_msg' => 'Modification d\'un champ d\'une liste',
            'liste_info' => $liste_info,
            'form' => $form->prepare()
        ]);
    }

    public function dupliquerFieldAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            return $this->retourListe('error', 'Action interdite', 'fields-liste');
        } else {
            if (array_key_exists('dupliquer', $prg)) {
                if (! (array_key_exists('id_liste', $prg) && array_key_exists('merge_id', $prg))) {
                    return $this->retourListe('error', 'Action interdite', 'fields-liste');
                }
            }
            if (array_key_exists('cancel', $prg)) {
                return $this->retourListe('warning', 'Le champ n\'a pas été dupliqué.', 'fields-liste');
            }
            $args = $prg;
        }
        $mailchimp = new MailChimp($this->config['mailchimp_key']);
        $form = new Form\Field(false);
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $params = $form->getDataForApi3();
                /**
                 * *******************************
                 * Attention à la syntaxe : opérateur 'merge-fields' et clés dans les
                 * paramètres ou le résultat 'merge_fields' et 'merge_id'
                 */
                $result = $mailchimp->post('lists/' . $args['id_liste'] . '/merge-fields', $params);
                if (! array_key_exists('merge_id', $result)) {
                    ob_start();
                    print_r($result);
                    $msg = ob_get_clean();
                    throw new \Exception($msg);
                }
                return $this->retourListe('success', 'Le champ a été dupliqué.', 'fields-liste');
            }
        } else {
            $result = $mailchimp->get('lists/' . $args['id_liste'] . '/merge-fields/' . $args['merge_id']);
            $form->setDataFromApi3($result);
        }
        // lecture des infos de la liste
        $liste_info = $mailchimp->get('lists/' . $args['id_liste']);
        if (! array_key_exists('id', $liste_info)) {
            ob_start();
            echo "La liste n'a pas été trouvée.\n";
            var_dump($liste_info);
            $message = ob_get_clean();
            throw new \Exception($message);
        }
        $view = new ViewModel([
            'h1_msg' => 'Créer un nouveau champ à partir d\'un autre',
            'liste_info' => $liste_info,
            'form' => $form->prepare()
        ]);
        $view->setTemplate('sbm-mail-chimp/index/edit-field.phtml');
        return $view;
    }

    public function creerFieldAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            return $this->retourListe('error', 'Action interdite', 'fields-liste');
        } else {
            if (array_key_exists('creer', $prg)) {
                if (! array_key_exists('id_liste', $prg)) {
                    return $this->retourListe('error', 'La liste n\'est pas précisée.', 'fields-liste');
                }
            }
            if (array_key_exists('cancel', $prg)) {
                return $this->retourListe('warning', 'Le champ n\'a pas été créé.', 'fields-liste');
            }
            $id_liste = $prg['id_liste'];
            $args = $prg;
        }
        $mailchimp = new MailChimp($this->config['mailchimp_key']);
        $form = new Form\Field(false);
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $params = $form->getDataForApi3();
                /**
                 * *******************************
                 * Attention à la syntaxe : opérateur 'merge-fields' et clés dans les
                 * paramètres ou le résultat 'merge_fields' et 'merge_id'
                 */
                $result = $mailchimp->post('lists/' . $id_liste . '/merge-fields', $params);
                if (! array_key_exists('merge_id', $result)) {
                    ob_start();
                    print_r($result);
                    echo $id_liste;
                    print_r($params);
                    $msg = ob_get_clean();
                    throw new \Exception($msg);
                }
                return $this->retourListe('success', 'Le champ a été créé.', 'fields-liste');
            }
        } else {
            /**
             * *****
             * Attention à la dénomination de ce champ dans l'API
             */
            $form->setDataFromApi3([
                'list_id' => $id_liste
            ]);
        }
        // lecture des infos de la liste
        $liste_info = $mailchimp->get('lists/' . $id_liste);
        if (! array_key_exists('id', $liste_info)) {
            ob_start();
            echo "La liste n'a pas été trouvée.\n";
            var_dump($liste_info, $id_liste, $args);
            $message = ob_get_clean();
            throw new \Exception($message);
        }
        $view = new ViewModel([
            'h1_msg' => 'Créer un nouveau champ',
            'liste_info' => $liste_info,
            'form' => $form->prepare()
        ]);
        $view->setTemplate('sbm-mail-chimp/index/edit-field.phtml');
        return $view;
    }

    public function supprFieldAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            return $this->retourListe('error', 'Action interdite', 'fields-liste');
        } else {
            if (array_key_exists('suppr', $prg)) {
                if (! (array_key_exists('id_liste', $prg) && array_key_exists('merge_id', $prg))) {
                    return $this->retourListe('error', 'Action interdite', 'fields-liste');
                }
            }
            if (array_key_exists('supprnon', $prg)) {
                return $this->retourListe('warning', 'Pas de suppression.', 'fields-liste');
            }
            $args = $prg;
        }
        $mailchimp = new MailChimp($this->config['mailchimp_key']);
        $form = new ButtonForm([
            'id_liste' => $args['id_liste'],
            'merge_id' => $args['merge_id']
        ], [
            'supproui' => [
                'class' => 'confirm',
                'value' => 'Confirmer'
            ],
            'supprnon' => [
                'class' => 'confirm',
                'value' => 'Abandonner'
            ]
        ]);
        if (array_key_exists('supproui', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $result = $mailchimp->delete('lists/' . $args['id_liste'] . '/merge-fields/' . $args['merge_id']);
                if (is_array($result)) {
                    ob_start();
                    print_r($result);
                    print_r($args);
                    $msg = ob_get_clean();
                    throw new \Exception($msg);
                }
                return $this->retourListe('success', sprintf('Le champ %s a été supprimé.', $args['merge_id']), 'fields-liste');
            }
        }
        $liste_info = $mailchimp->get('lists/' . $args['id_liste']);
        
        $view = new ViewModel([
            'liste_info' => $liste_info,
            'form' => $form->prepare(),
            'field_name' => $args['field_name'],
            'merge_id' => $args['merge_id']
        ]);
        $view->setTemplate('sbm-mail-chimp/index/suppr-liste.phtml');
        return $view;
    }

    /**
     * Gestion des segments
     *
     * L'entrée par post doit fournir les paramètres
     * - id_liste
     * - liste_name
     * ou
     * - retour (si on a reçu `retour` alors on récupère les 2 autres paramètres en session)
     *
     * L'entrée par get récupère les paramètres en session.
     *
     * @see https://developer.mailchimp.com/documentation/mailchimp/reference/lists/segments/
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function segmentsListeAction()
    {
        // controle d'entrée
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('identifiant', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->retourListe();
            }
        } else {
            if (array_key_exists('id_liste', $prg)) {
                if (array_key_exists('segments', $prg)) {
                    $this->setToSession('identifiant', [
                        'id_liste' => $prg['id_liste'],
                        'liste_name' => $prg['liste_name']
                    ], $this->getSessionNamespace());
                }
            } elseif (! array_key_exists('retour', $prg)) {
                return $this->retourListe();
            }
            // on s'assure que les 2 paramètres sont dans args (cas d'un retour)
            $args = array_merge($this->getFromSession('identifiant', [], $this->getSessionNamespace()), $prg);
        }
        $mailchimp = new MailChimp($this->config['mailchimp_key']);
        // lecture des infos de la liste
        $liste_info = $mailchimp->get('lists/' . $args['id_liste']);
        if (! array_key_exists('id', $liste_info)) {
            ob_start();
            echo "La liste n'a pas été trouvée.\n";
            var_dump($liste_info);
            $message = ob_get_clean();
            throw new \Exception($message);
        }
        // lecture des segments
        $method = 'lists/' . $args['id_liste'] . '/segments';
        $source = new Paginator(new MailChimpAdapter($mailchimp, $method, 'segments'));
        if (! $source->count()) {
            $message = 'Il n`y a pas de segment pour cette liste.<br>Il faut les créer.';
        } else {
            $message = '';
        }
        return new ViewModel([
            'liste_info' => $liste_info,
            'source' => $source,
            'auth' => $this->config['authenticate']->by('email'),
            'acl' => $this->config['acl'],
            'message' => $message
        ]);
    }

    /**
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function creerSegmentAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            return $this->retourListe('error', 'Action interdite', 'segments-liste');
        } else {
            if (array_key_exists('creer', $prg)) {
                if (! array_key_exists('id_liste', $prg)) {
                    return $this->retourListe('error', 'La liste n\'est pas précisée.', 'segments-liste');
                }
            }
            if (array_key_exists('cancel', $prg)) {
                return $this->retourListe('warning', 'Le segment n\'a pas été créé.', 'segments-liste');
            }
            $id_liste = $prg['id_liste'];
            $args = $prg;
        }
        $mailchimp = new MailChimp($this->config['mailchimp_key']);
        $form = new Form\Segment();
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $params = $form->getDataForApi3(true);
                $result = $mailchimp->post('lists/' . $id_liste . '/segments', $params);
                if (! array_key_exists('id', $result)) {
                    ob_start();
                    print_r($result);
                    echo $id_liste;
                    print_r($params);
                    $msg = ob_get_clean();
                    throw new \Exception($msg);
                }
                return $this->retourListe('success', 'Le segment a été créé. Il faut maintenant ajouter les règles de filtrage.', 'segments-liste');
            }
        } else {
            /**
             * *****
             * Attention à la dénomination de ce champ dans l'API
             */
            $form->setDataFromApi3([
                'list_id' => $id_liste
            ]);
        }
        // lecture des infos de la liste
        $liste_info = $mailchimp->get('lists/' . $id_liste);
        if (! array_key_exists('id', $liste_info)) {
            ob_start();
            echo "La liste '$id_liste' n'a pas été trouvée.\n";
            var_dump($liste_info, $id_liste, $args);
            $message = ob_get_clean();
            throw new \Exception($message);
        }
        $view = new ViewModel([
            'h1_msg' => 'Créer un nouveau segment dans une liste',
            'liste_info' => $liste_info,
            'form' => $form->prepare()
        ]);
        $view->setTemplate('sbm-mail-chimp/index/edit-segment.phtml');
        return $view;
    }

    /**
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function dupliquerSegmentAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            return $this->retourListe('error', 'Action interdite', 'segments-liste');
        } else {
            if (array_key_exists('dupliquer', $prg)) {
                if (! (array_key_exists('id_liste', $prg) && array_key_exists('segment_id', $prg))) {
                    return $this->retourListe('error', 'Action interdite', 'segments-liste');
                }
            }
            if (array_key_exists('cancel', $prg)) {
                return $this->retourListe('warning', 'Le segment n\'a pas été dupliqué.', 'segments-liste');
            }
            $args = $prg;
        }
        $mailchimp = new MailChimp($this->config['mailchimp_key']);
        $source = $mailchimp->get('lists/' . $args['id_liste'] . '/segments/' . $args['segment_id']);
        $form = new Form\Segment(StdLib::getParamR([
            'options',
            'conditions'
        ], $source), []);
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $params = $form->getDataForApi3(true);
                $result = $mailchimp->post('lists/' . $args['id_liste'] . '/segments', $params);
                if (! array_key_exists('id', $result)) {
                    ob_start();
                    print_r($result);
                    $msg = ob_get_clean();
                    throw new \Exception($msg);
                }
                return $this->retourListe('success', 'Le segment a été dupliqué.', 'segments-liste');
            }
        } else {
            $form->setDataFromApi3($source);
        }
        // lecture des infos de la liste
        $liste_info = $mailchimp->get('lists/' . $args['id_liste']);
        if (! array_key_exists('id', $liste_info)) {
            ob_start();
            echo "La liste n'a pas été trouvée.\n";
            var_dump($liste_info);
            $message = ob_get_clean();
            throw new \Exception($message);
        }
        $view = new ViewModel([
            'h1_msg' => 'Créer un segment champ à partir d\'un autre',
            'liste_info' => $liste_info,
            'form' => $form->prepare()
        ]);
        $view->setTemplate('sbm-mail-chimp/index/edit-segment.phtml');
        return $view;
    }

    /**
     * Doit recevoir les paramètres `id_liste` et `segment_id` ou les retrouver en session.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editSegmentAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('identifiant', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->retourListe('error', 'Action interdite.', 'segments-liste');
            }
        } else {
            if (array_key_exists('edit', $prg)) {
                if (array_key_exists('id_liste', $prg) && array_key_exists('segment_id', $prg)) {
                    $this->setToSession('identifiant', [
                        'id_liste' => $prg['id_liste'],
                        'segment_id' => $prg['segment_id']
                    ], $this->getSessionNamespace());
                } else {
                    return $this->retourListe('error', 'Action interdite.', 'segments-liste');
                }
            }
            if (array_key_exists('cancel', $prg)) {
                return $this->retourListe('warning', 'Pas de modification.', 'segments-liste');
            }
            $args = $prg;
        }
        $mailchimp = new MailChimp($this->config['mailchimp_key']);
        $form = new Form\Segment();
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $params = $form->getDataForApi3();
                $result = $mailchimp->patch('lists/' . $args['id_liste'] . '/segments/' . $args['segment_id'], $params);
                if (! array_key_exists('id', $result)) {
                    ob_start();
                    print_r($result);
                    print_r($params);
                    $msg = ob_get_clean();
                    throw new \Exception($msg);
                }
                return $this->retourListe('success', 'Modification enregistrée.', 'segments-liste');
            }
        } else {
            $segment = $mailchimp->get('lists/' . $args['id_liste'] . '/segments/' . $args['segment_id']);
            $form->setDataFromApi3($segment);
        }
        // lecture des infos de la liste
        $liste_info = $mailchimp->get('lists/' . $args['id_liste']);
        if (! array_key_exists('id', $liste_info)) {
            ob_start();
            echo "La liste n'a pas été trouvée.\n";
            var_dump($liste_info);
            $message = ob_get_clean();
            throw new \Exception($message);
        }
        return new ViewModel([
            'h1_msg' => 'Modification d\'un segment d\'une liste',
            'liste_info' => $liste_info,
            'id_liste' => $args['id_liste'],
            'segment_id' => $args['segment_id'],
            'form' => $form->prepare(),
            'segment' => $segment
        ]);
    }

    /**
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function supprSegmentAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            return $this->retourListe('error', 'Action interdite', 'segments-liste');
        } else {
            if (array_key_exists('suppr', $prg)) {
                if (! (array_key_exists('id_liste', $prg) && array_key_exists('segment_id', $prg))) {
                    return $this->retourListe('error', 'Action interdite', 'segments-liste');
                }
            }
            if (array_key_exists('supprnon', $prg)) {
                return $this->retourListe('warning', 'Pas de suppression.', 'segments-liste');
            }
            $args = $prg;
        }
        $mailchimp = new MailChimp($this->config['mailchimp_key']);
        $form = new ButtonForm([
            'id_liste' => $args['id_liste'],
            'segment_id' => $args['segment_id']
        ], [
            'supproui' => [
                'class' => 'confirm',
                'value' => 'Confirmer'
            ],
            'supprnon' => [
                'class' => 'confirm',
                'value' => 'Abandonner'
            ]
        ]);
        if (array_key_exists('supproui', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $result = $mailchimp->delete('lists/' . $args['id_liste'] . '/segments/' . $args['segment_id']);
                if (is_array($result)) {
                    ob_start();
                    print_r($result);
                    print_r($args);
                    $msg = ob_get_clean();
                    throw new \Exception($msg);
                }
                return $this->retourListe('success', sprintf('Le segment %s a été supprimé.', $args['segment_id']), 'segments-liste');
            }
        }
        $liste_info = $mailchimp->get('lists/' . $args['id_liste']);
        $view = new ViewModel([
            'liste_info' => $liste_info,
            'form' => $form->prepare(),
            'segment_name' => $args['segment_name'],
            'segment_id' => $args['segment_id']
        ]);
        $view->setTemplate('sbm-mail-chimp/index/suppr-liste.phtml');
        return $view;
    }

    /**
     * Affiche la liste des membres d'un segment
     *
     * Pour une entrée par post, reçoit les paramètres
     * - id_liste (obligatoire)
     * - liste_name
     * - segment_id (obligatoire)
     * - segment_name
     * Si ces paramètres sont absents, ils doivent se trouver en session.
     *
     * Pour une entrée par get, ces paramètres doivent être en session.
     * (entrée par get nécessaire à cause du paginator)
     *
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function segmentMembersAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('identifiant', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->retourListe('error', 'Action interdite.', 'segments-liste');
            }
        } else {
            if (array_key_exists('id_liste', $prg) && array_key_exists('segment_id', $prg)) {
                if (array_key_exists('members', $prg)) {
                    $this->setToSession('identifiant', [
                        'id_liste' => $prg['id_liste'],
                        'liste_name' => $prg['liste_name'],
                        'segment_id' => $prg['segment_id'],
                        'segment_name' => $prg['segment_name']
                    ], $this->getSessionNamespace());
                }
            } elseif (! array_key_exists('retour', $prg)) {
                return $this->retourListe('error', 'Action interdite.', 'segments-liste');
            }
            if (array_key_exists('cancel', $prg)) {
                return $this->retourListe('warning', 'Pas de modification.', 'segments-liste');
            }
            // on s'assure que les 4 paramètres sont dans args
            $args = array_merge($this->getFromSession('identifiant', [], $this->getSessionNamespace()), $prg);
        }
        $mailchimp = new MailChimp($this->config['mailchimp_key']);
        $method = 'lists/' . $args['id_liste'] . '/segments/' . $args['segment_id'] . '/members';
        $source = new Paginator(new MailChimpAdapter($mailchimp, $method, 'members'));
        if (! $source->count()) {
            return $this->retourListe('warning', 'Pas de membre dans ce segment.', 'segments-liste');
        }
        return new ViewModel([
            'source' => $source,
            'id_liste' => $args['id_liste'],
            'liste_name' => $args['liste_name'],
            'segment_name' => $args['segment_name'],
            'auth' => $this->config['authenticate']->by('email'),
            'acl' => $this->config['acl'],
            'page' => $this->params('page', 1)
        ]);
    }

    /**
     * Affiche la liste des membres d'une liste
     *
     * Pour une entrée par post, reçoit les paramètres
     * - id_liste (obligatoire)
     * - liste_name
     * Si ces paramètres sont absents, ils doivent se trouver en session.
     *
     * Pour une entrée par get, ces paramètres doivent être en session.
     * (entrée par get nécessaire à cause du paginator)
     *
     * @see https://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/
     */
    public function listeMembersAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('identifiant', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->retourListe();
            }
        } else {
            if (array_key_exists('id_liste', $prg)) {
                if (array_key_exists('members', $prg)) {
                    $this->setToSession('identifiant', [
                        'id_liste' => $prg['id_liste'],
                        'liste_name' => $prg['liste_name']
                    ], $this->getSessionNamespace());
                }
            } elseif (! array_key_exists('retour', $prg)) {
                return $this->retourListe();
            }
            if (array_key_exists('cancel', $prg)) {
                return $this->retourListe('warning', 'Pas de modification.');
            }
            // on s'assure que les 2 paramètres sont dans args
            $args = array_merge($this->getFromSession('identifiant', [], $this->getSessionNamespace()), $prg);
        }
        $mailchimp = new MailChimp($this->config['mailchimp_key']);
        $method = 'lists/' . $args['id_liste'] . '/members';
        $source = new Paginator(new MailChimpAdapter($mailchimp, $method, 'members'));
        if (! $source->count()) {
            $message = 'Pas de membre dans cette liste.<br>Il faut mettre la liste à jour.';
        } else {
            $message = '';
        }
        $view = new ViewModel([
            'source' => $source,
            'id_liste' => $args['id_liste'],
            'liste_name' => $args['liste_name'],
            'message' => $message,
            'auth' => $this->config['authenticate']->by('email'),
            'acl' => $this->config['acl'],
            'page' => $this->params('page', 1)
        ]);
        $view->setTemplate('sbm-mail-chimp/index/segment-members');
        return $view;
    }

    /**
     * Mise à jour des membres de la liste (par la méthode de put)
     *
     * Reçoit en post l'identifiant de la liste 'id_liste'.
     * Eventuellement, reçoit en post les paramètres 'populate' ou 'selection'.
     *
     * Les données sont extraites de la base de données par la méthode
     * SbmMailChimp\Model\Db\Service\Users::getMembersForMailChimpListe()
     */
    public function populateAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            return $this->retourListe('info', 'Choisissez une action (1).', 'liste-members');
        } elseif (array_key_exists('cancel', $prg)) {
            return $this->retourListe('warning', 'Abandon. La liste n\'a pas été mise à jour.');
        } elseif (array_key_exists('id_liste', $prg) && array_key_exists('populate', $prg)) {
            /**
             * ALGORITHME
             * créer un batch
             * lancer la requête sur la table users
             * pour chaque résultat de la requête
             * - placer le résultat en put dans le batch
             * lancer l'exécution du batch
             * retour à la liste des membres
             */
            $id_list = $prg['id_liste'];
            $mailchimp = new MailChimp($this->config['mailchimp_key']);
            $batch = $mailchimp->new_batch();
            $query = $this->config['db_manager']->get(Users::class);
            if (getenv('APPLICATION_ENV') == 'development') {
                $limit = 50;
            } else {
                $limit = 0;
            }
            $result = $query->getMembersForMailChimpListe($limit);
            $i = 0;
            foreach ($result as $member) {
                $email_address = $member['email_address'];
                $subscriber_hash = md5(strtolower($email_address));
                $merge_fields = (array) $member;
                unset($merge_fields['email_address']);
                $params = [
                    'email_address' => $email_address,
                    'status_if_new' => 'subscribed',
                    'merge_fields' => $merge_fields
                ];
                $id = 'op' . ++ $i;
                $batch->put($id, "lists/$id_list/members/$subscriber_hash", $params);
            /**
             * Adapter la view pour pouvoir controler l'exécution du batch
             */
            }
            $cr = $batch->execute();
            return new ViewModel([
                'compte_rendu' => $cr
            ]);
        } else {
            return $this->retourListe('info', 'Choisissez une action (2).', 'liste-members');
        }
    }

    /**
     * Reçoit en post un id_batch
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function controleAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            return $this->retourListe('info', 'Choisissez une action (3).', 'liste-members');
        }
        if (array_key_exists('id_batch', $prg)) {
            $id_batch = $prg['id_batch'];
            // $id_batch = '7a26590b5a';
            $mailchimp = new MailChimp($this->config['mailchimp_key']);
            $batch = $mailchimp->new_batch($id_batch);
            $cr = $batch->check_status();
        }
        $view = new ViewModel([
            'compte_rendu' => $cr
        ]);
        $view->setTemplate('sbm-mail-chimp/index/populate');
        return $view;
    }

    /**
     * Suppression des membres de la liste qui ne sont plus dans sbm.
     *
     * Il faudra utiliser la date de mise à jour.
     */
    public function cleanAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            return $this->retourListe('info', 'Choisissez une action (1).', 'liste-members');
        }
        if (array_key_exists('id_liste', $prg) && array_key_exists('clean', $prg)) {
            
            /**
             * ALGORITHME
             * lire les members et garder le tableau des email_address
             * créer un batch
             * pour chaque email de ce tableau
             * - rechercher cet email dans la table users
             * - si l'email n'y est pas, demander la suppression par batch
             * lancer l'exécution du batch
             */
            // lire les members et garder le tableau des email_address
            $id_list = $prg['id_liste'];
            $method = "lists/$id_list/members";
            $container = 'members';
            $mailchimp = new MailChimp($this->config['mailchimp_key']);
            $total_items = StdLib::getParam('total_items', $mailchimp->get($method));
            $method = sprintf('%s?offset=%d&count=%d', $method, 0, $total_items);
            $members = StdLib::getParam('members', $mailchimp->get($method), []);
            $emails = [];
            foreach ($members as $member) {
                $emails[] = $member['email_address'];
            }
            unset($members);
            // créer un batch
            $batch = $mailchimp->new_batch();
            $contenu = false;
            // pour chaque email
            $tusers = $this->config['db_manager']->get('Sbm\Db\Table\Users');
            $i = 0;
            foreach ($emails as $email) {
                try {
                    $testId = $tusers->getRecordByEmail($email);
                } catch (\SbmCommun\Model\Db\Service\Table\Exception $e) {
                    $subscriber_hash = md5(strtolower($email));
                    $method = "lists/$id_list/members/$subscriber_hash";
                    $id = 'op' . ++ $i;
                    $batch->delete($id, $method);
                    $contenu = true;
                }
            }
            // lancer l'exécution du batch si nécessaire
            if ($contenu) {
                $cr = $batch->execute();
                $view = new ViewModel([
                    'compte_rendu' => $cr
                ]);
                $view->setTemplate('sbm-mail-chimp/index/populate');
                return $view;
            } else {
                return $this->retourListe('info', 'La liste est à jour.', 'liste-members');
            }
        } else {
            return $this->retourListe('info', 'Choisissez une action (2).', 'liste-members');
        }
    }
}
 