<?php
/**
 * Plugin pour la plateforme de paiement SystemPay
 *
 * La méthode `notification($data)` vérifie la signature puis lance un évènement 'paiementNotification' 
 * avec comme `argv` le tableau des vads_ contenus dans $data
 * 
 * La version 2 (2015) abandonne l'exploitation des champs `vads_nb_products` et `vads_product_refN` qui 
 * ne sont pas renvoyés dans la notification. 
 * Ajout de la méthode getUniqueId() et modification de la méthode prepareData()
 * 
 * La version 3 (2016) abandonne le contexte (target) qui contenait le service manager et devient 
 * compatible ZF3.
 * 
 * @project sbm
 * @package SbmPaiement/Plugin/SystemPay
 * @filesource Plateforme.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmPaiement\Plugin\SystemPay;

use SbmPaiement\Plugin\AbstractPlateforme;
use SbmPaiement\Plugin\Exception;
use Zend\Db\Sql\Where;
use Zend\Form\Form;
use Zend\Stdlib\Parameters;

class Plateforme extends AbstractPlateforme
{

    private $certificat;

    /**
     * Initialise le certificat et complète la propriété config par la config locale du plugin
     *
     * (non-PHPdoc)
     *
     * @see \SbmPaiement\Plugin\AbstractPlateforme::init()
     */
    protected function init()
    {
        $this->setConfig(
            array_merge($this->getPlateformeConfig(),
                include __DIR__ . '/config/systempay.config.php'));
        $this->certificat = $this->getParam(
            [
                'certificat',
                $this->getParam('vads_ctx_mode')
            ]);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \SbmPaiement\Plugin\AbstractPlateforme::validNotification()
     */
    protected function validNotification(Parameters $data, $remote_addr = '')
    {
        $data = $data->toArray();
        $signature = $this->getSignature($data);
        if (! array_key_exists('signature', $data) || $signature != $data['signature']) {
            $this->error_no = 1001;
            $this->error_msg = 'Erreur de signature : ' . $signature;
            return false;
        }
        if (isset($data['vads_result'])) {
            switch ($data['vads_result']) {
                case '00':
                case '05':
                    return true;
                case '30':
                    $this->error_no = 1030;
                    if (isset($data['vads_extra_result'])) {
                        $champs = include 'config/vads.inc.php';
                        if (isset($champs[$data['vads_extra_result']])) {
                            $this->error_msg = sprintf(
                                'Erreur de la requête dans le champ %s',
                                $champs[$data['vads_extra_result']]);
                        } else {
                            $this->error_msg = sprintf('Erreur %s dans la requête',
                                $data['vads_extra_result']);
                        }
                    } else {
                        $this->error_msg = 'Erreur de format de la requête.';
                    }
                case '96':
                    $this->error_no = 1096;
                    $this->error_msg = 'Erreur technique';
                    break;
                default:
                    $this->error_no = 1255;
                    $this->error_msg = 'Erreur inconnue';
                    break;
            }
            return false;
        }
        return true;
    }

    /**
     * On vérifie que :
     * - vads_result == 00
     * - vads_trans_status == AUTHORISED
     * - vads_payment_certificate est de longueur 40
     *
     * (non-PHPdoc)
     *
     * @see \SbmPaiement\Plugin\AbstractPlateforme::validPaiement()
     */
    protected function validPaiement()
    {
        if ($this->data['vads_result'] != '00') {
            $this->error_msg = 'Action refusée.';
            $this->error_no = 2005;
            return false;
        }
        if ($this->data['vads_trans_status'] != 'AUTHORISED') {
            $this->error_msg = '(trans_status)';
            $this->error_no = 2006;
            return false;
        }
        if (empty($this->data['vads_payment_certificate']) ||
            strlen($this->data['vads_payment_certificate']) != 40) {
            $this->error_msg = 'Certificat de paiement invalide';
            $this->error_no = 2007;
            return false;
        }
        // le DEBIT ou le CREDIT est correct
        $table = $this->getDbManager()->get('SbmPaiement\Plugin\Table');
        $objectData = $table->getObjData();
        $this->data['systempayId'] = null;

        // référence des élèves concernés
        $nb_ref = $this->data['vads_nb_products'];
        for ($eleveIds = [], $i = 0; $i < $nb_ref; $i ++) {
            $eleveIds[] = $this->data['vads_product_ref' . $i];
        }
        $this->data['ref_eleveIds'] = implode('-', $eleveIds);

        // enregistrement
        $objectData->exchangeArray($this->data);
        try {
            $table->saveRecord($objectData);

            $this->error_no = 0;
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            while ($e && ! $e instanceof \PDOException)
                $e = $e->getPrevious();
            if ($e->getCode() == 23000) {
                $this->error_msg = 'Duplicate Entry';
                $this->error_no = 23000;
            } else {
                throw new Exception(
                    'Erreur lors de l\'enregistrement de la notification de paiement.',
                    $e->getCode(), $e);
            }
        }
        return true;
    }

    /**
     * Modifié le 18 mai 2015 en raison de l'absence des champs vads_nb_products et
     * vads_product_refN dans la réponse.
     * Utilisation de la table `appels` pour retrouver les élèves concernés.
     * ATTENTION ! this->data est Zend\Stdlib\Parameters
     *
     * (non-PHPdoc)
     *
     * @see \SbmPaiement\Plugin\AbstractPlateforme::prepareData()
     */
    protected function prepareData()
    {
        $this->paiement = [
            'type' => $this->data['vads_operation_type'],
            'paiement' => [
                'datePaiement' => \DateTime::createFromFormat('YmdHis',
                    $this->data['vads_trans_date'])->format('Y-m-d H:i:s'),
                'dateValeur' => \DateTime::createFromFormat('YmdHis',
                    $this->data['vads_effective_creation_date'])->format('Y-m-d H:i:s'),
                'responsableId' => $this->data['vads_cust_id'],
                'anneeScolaire' => $this->getAnneeScolaire(),
                'exercice' => $this->getExercice(),
                'montant' => $this->data['vads_amount'],
                'codeModeDePaiement' => $this->getCodeModeDePaiement(),
                'codeCaisse' => $this->getCodeCaisse(),
                'reference' => $this->data['vads_trans_id']
            ]
        ];
        $this->scolarite = [
            'type' => $this->data['vads_operation_type'],
            'millesime' => $this->getMillesime(),
            'eleveIds' => []
        ];
        /**
         * Abandon de cette partie en raison de l'absence de ces champs dans la notification
         *
         * $nb_ref = $this->data['vads_nb_products'];
         * for ($i = 0; $i < $nb_ref; $i ++) {
         * $this->scolarite['eleveIds'][] = $this->data['vads_product_ref' . $i];
         * }
         */
        // pour DEBUG
        /*
         * if (is_array($this->data)) {
         * $msg = 'this->data est un array';
         * } elseif (is_object($this->data)) {
         * $msg = 'this->data est ' . get_class($this->data);
         * } else {
         * $msg = gettype($this->data);
         * }
         * $this->logError(Logger::INFO, $msg, $this->data);
         */

        $tAppels = $this->getDbManager()->get('Sbm\Db\Table\Appels');
        $where = new Where();
        // ATTENTION ! this->data est Zend\Stdlib\Parameters
        $where->equalTo('referenceId', $this->getUniqueId($this->data->toArray()));
        $rowset = $tAppels->fetchAll($where);
        foreach ($rowset as $row) {
            $this->scolarite['eleveIds'][] = $row->eleveId;
        }
    }

    /**
     * Renvoie un nombre de 000001 à 899999
     * En fait, la valeur maxi est limitée par config['vads_trans_id_max']
     *
     * @return string
     */
    private function getVadsTransId()
    {
        $size = 0;
        $fp = fopen($this->getParam('uniqid_path') . '/uniqid.txt', 'a');
        if ($fp && flock($fp, LOCK_EX)) {
            fwrite($fp, '.');
            $stat = fstat($fp);
            $size = $stat['size'];
            if ($size > $this->getParam('vads_trans_id_max')) {
                ftruncate($fp, 1);
                $size = 1;
            }
            flock($fp, LOCK_UN);
            fclose($fp);
        } else {
            throw new Exception("Impossible d\'ouvrir le fichier 'uniqid.txt'.");
        }
        return sprintf('%06d', $size);
    }

    /**
     * Calcule et renvoie la signature à partir des data fournis et du certificat
     *
     * @param array $data
     *
     * @return string
     */
    private function getSignature($data)
    {
        ksort($data);
        $str = '';
        foreach ($data as $key => $value) {
            if (substr($key, 0, 5) == 'vads_') {
                $str .= "$value+";
            }
        }
        $str .= $this->certificat;
        return sha1($str);
    }

    public function getUrl()
    {
        return $this->getParam('url_paiement');
    }

    /**
     * Enregistre la demande d'appel à paiement dans la table `appels` permettant d'associer un
     * paiement à un responsable et des enfants et renvoie un formulaire avec les valeurs affectées
     *
     * @param array $params
     * @return \Zend\Form\Form
     */
    public function getForm(array $params)
    {
        $form = new Form('plugin-formulaire');
        // préparation du formulaire
        $args = $this->prepareAppel($params);
        foreach ($args as $key => $value) {
            $form->add(
                [
                    'type' => 'hidden',
                    'name' => $key,
                    'attributes' => [
                        'value' => $value
                    ]
                ]);
        }
        $form->setAttribute('action', $this->getUrl());

        // enregistrement de l'appel à paiement
        $id = $this->getUniqueId($args);
        $tAppels = $this->db_manager->get('Sbm\Db\Table\Appels');
        $odata = $tAppels->getObjData();
        foreach ($params['elevesIds'] as $eleveId) {
            $odata->exchangeArray(
                [
                    'referenceId' => $id,
                    'responsableId' => $params['responsableId'],
                    'eleveId' => $eleveId
                ]);
            $tAppels->saveRecord($odata);
        }

        return $form;
    }

    /**
     * En fonction de la description technique du paquet à envoyer à la plate-forme de paiement
     * (voir Guide d'implementation du formulaire de paiement Systempay 2.2 - doc version 3.0)
     *
     * A noter que contrairement à ce que dit la documentation, les champs vads_nb_products et
     * vads_product_refN ne sont pas renvoyés dans le réponse. Aussi, une table des appels à
     * la plateforme enregistrera les rérérences des élèves concernés, pour pouvoir être traités
     * au retour de la notification. Une clé unique de paiement doit être constituée et renvoyée
     * par la méthode getUniqueId()
     *
     * (non-PHPdoc)
     *
     * @see \SbmPaiement\Plugin\PlateformeInterface::prepareAppel()
     */
    public function prepareAppel($params)
    {
        $champs = [
            'vads_site_id' => $this->getParam('vads_site_id'),
            'vads_ctx_mode' => $this->getParam('vads_ctx_mode'),
            'vads_trans_id' => $this->getVadsTransId(),
            'vads_trans_date' => gmdate('YmdHis'),
            'vads_amount' => sprintf('%d', $params['montant'] * 100),
            'vads_currency' => $this->getParam('vads_currency'),
            'vads_action_mode' => $this->getParam('vads_action_mode'),
            'vads_page_action' => $this->getParam('vads_page_action'),
            'vads_version' => $this->getParam('vads_version'),
            'vads_payment_config' => $this->getVadsPaymentConfig($params),
            'vads_capture_delay' => $this->getParam('vads_capture_delay'),
            'vads_validation_mode' => $this->getParam('vads_validation_mode'),
            'vads_cust_email' => $params['email'],
            'vads_cust_id' => $params['responsableId'],
            'vads_cust_first_name' => $params['prenom'],
            'vads_cust_last_name' => $params['nom'],
            'vads_order_id' => sprintf('TS%04d-%s-%011d-%d', $this->getMillesime(),
                date('Ymd'), $params['responsableId'], count($params['eleveIds'])),
            'vads_theme_config' => $this->getParam('vads_theme_config'),
            'vads_url_success' => $this->getParam('vads_url_success'),
            'vads_url_refused' => $this->getParam('vads_url_refused'),
            'vads_url_cancel' => $this->getParam('vads_url_cancel'),
            'vads_url_error' => $this->getParam('vads_url_error'),
            'vads_url_check' => $this->getParam('vads_url_check'),
            // 'vads_redirect_success_timeout' => $this->getParam('vads_redirect_success_timeout'),
            // 'vads_redirect_success_message' => $this->getParam('vads_redirect_success_message'),
            // 'vads_redirect_error_timeout' => $this->getParam('vads_redirect_error_timeout'),
            // 'vads_redirect_error_message' => $this->getParam('vads_redirect_error_message'),
            'vads_nb_products' => sprintf('%d', count($params['eleveIds']))
        ];
        for ($i = 0; $i < count($params['eleveIds']); $i ++) {
            $champs['vads_product_ref' . ($i)] = $params['eleveIds'][$i];
        }
        $result = [];
        foreach ($champs as $key => $value) {
            if ($value != '')
                $result[$key] = $value;
        }
        $result['signature'] = $this->getSignature($result);

        return $result;
    }

    /**
     * En fonction de la documentation, renvoie une clé unique de demande de transaction.
     * Ici, vads_trans_id est unique sur la journée. On concatène donc à la date pour
     * obtenir une clé unique.
     *
     * @param array $vadsPaquet
     * @return string
     */
    public function getUniqueId(array $vadsPaquet)
    {
        return $vadsPaquet['vads_trans_id'] . $vadsPaquet['vads_trans_date'];
    }

    private function getVadsPaymentConfig($params)
    {
        if ($params['count'] == 1) {
            return 'SINGLE';
        } else {
            return sprintf('MULTI:first=%d;count=%d;period=%d', $params['first'] * 100,
                $params['count'], $params['period']);
        }
    }
}