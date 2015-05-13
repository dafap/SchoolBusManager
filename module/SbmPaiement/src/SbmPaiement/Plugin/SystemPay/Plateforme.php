<?php
/**
 * Plugin pour la plateforme de paiement SystemPay
 *
 * La méthode `notification($data)` vérifie la signature puis lance un évènement 'paiementNotification' avec 
 * comme `target` le ServiceManager et comme `argv` le tableau des vads_ contenus dans $data
 * 
 * @project sbm
 * @package SbmPaiement/Plugin/SystemPay
 * @filesource Plateforme.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 mars 2015
 * @version 2015-1
 */
namespace SbmPaiement\Plugin\SystemPay;

use Zend\Stdlib\Parameters;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\Log\Logger;
use SbmCommun\Model\StdLib;
use SbmPaiement\Plugin\AbstractPlateforme;
use SbmPaiement\Plugin\Exception;

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
        $this->setConfig(array_merge($this->getPlateformeConfig(), include __DIR__ . '/config/systempay.config.php'));
        $this->certificat = $this->getParam(array(
            'certificat',
            $this->getParam('vads_ctx_mode')
        ));
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
        if (!array_key_exists('signature', $data) || $signature != $data['signature']) {
            $this->error_no = 1001;
            $this->error_msg = 'Erreur de signature';
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
                            $this->error_msg = sprintf('Erreur de la requête dans le champ %s', $champs[$data['vads_extra_result']]);
                        } else {
                            $this->error_msg = sprintf('Erreur %s dans la requête', $data['vads_extra_result']);
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
        if (empty($this->data['vads_payment_certificate']) || strlen($this->data['vads_payment_certificate']) != 40) {
            $this->error_msg = 'Certificat de paiement invalide';
            $this->error_no = 2007;
            return false;
        }
        // le DEBIT ou le CREDIT est correct
        $table = $this->getServiceLocator()->get('SbmPaiement\Plugin\Table');
        $objectData = $table->getObjData();
        $this->data['systempayId'] = null;
        
        // référence des élèves concernés
        $nb_ref = $this->data['vads_nb_product'];
        for ($eleveIds = array(), $i = 1; $i <= $nb_ref; $i ++) {
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
                throw new Exception('Erreur lors de l\'enregistrement de la notification de paiement.', $e->getCode(), $e);
            }
        }
        return true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \SbmPaiement\Plugin\AbstractPlateforme::prepareData()
     */
    protected function prepareData()
    {
        $this->paiement = array(
            'type' => $this->data['vads_operation_type'],
            'paiement' => array(
                'datePaiement' => \DateTime::createFromFormat('YmdHis', $this->data['vads_trans_date'])->format('Y-m-d H:i:s'),
                'dateValeur' => \DateTime::createFromFormat('YmdHis', $this->data['vads_effective_creation_date'])->format('Y-m-d H:i:s'),
                'responsableId' => $this->data['vads_cust_id'],
                'anneeScolaire' => $this->getAnneeScolaire(),
                'exercice' => $this->getExercice(),
                'montant' => $this->data['vads_amount'],
                'codeModeDePaiement' => $this->getCodeModeDePaiement(),
                'codeCaisse' => $this->getCodeCaisse(),
                'reference' => $this->data['vads_trans_id']
            )
        );
        $this->scolarite = array(
            'type' => $this->data['vads_operation_type'],
            'millesime' => $this->getMillesime(),
            'eleveIds' => array()
        );
        $nb_ref = $this->data['vads_nb_product'];
        for ($i = 1; $i <= $nb_ref; $i ++) {
            $this->scolarite['eleveIds'][] = $this->data['vads_product_ref' . $i];
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
        if (($fp = fopen($this->getParam('uniqid_path') . '/uniqid.txt', 'a')) && flock($fp, LOCK_EX)) {
            fwrite($fp, '.');
            $stat = fstat($fp);
            $size = $stat['size'];
            if ($size > $this->getParam('vads_trans_id_max')) {
                ftruncate($fp, 1);
                $size = 1;
            }
            flock($fp, LOCK_UN);
            fclose($fp);
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

    public function prepareAppel($params)
    {
        $champs = array(
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
            'vads_order_id' => sprintf('TS%04d-%s-%011d-%d', $this->getMillesime(), date('Ymd'), $params['responsableId'], count($params['eleveIds'])),
            'vads_theme_config' => $this->getParam('vads_theme_config'),
            'vads_url_success' => $this->getParam('vads_url_success'),
            'vads_url_refused' => $this->getParam('vads_url_refused'),
            'vads_url_cancel' => $this->getParam('vads_url_cancel'),
            'vads_url_error' => $this->getParam('vads_url_error'),
            'vads_url_check' => $this->getParam('vads_url_check'),
            //'vads_redirect_success_timeout' => $this->getParam('vads_redirect_success_timeout'),
            //'vads_redirect_success_message' => $this->getParam('vads_redirect_success_message'),
            //'vads_redirect_error_timeout' => $this->getParam('vads_redirect_error_timeout'),
            //'vads_redirect_error_message' => $this->getParam('vads_redirect_error_message'),
            'vads_nb_products' => sprintf('%d', count($params['eleveIds']))
        );
        for ($i = 0; $i < count($params['eleveIds']); $i ++) {
            $champs['vads_product_ref' . ($i)] = $params['eleveIds'][$i];
        }
        $result = array();
        foreach ($champs as $key => $value) {
            if ($value != '')
                $result[$key] = $value;
        }
        $result['signature'] = $this->getSignature($result);
        //die(var_dump($result));
        
        return $result;
    }

    private function getVadsPaymentConfig($params)
    {
        if ($params['count'] == 1) {
            return 'SINGLE';
        } else {
            return sprintf('MULTI:first=%d;count=%d;period=%d', $params['first'] * 100, $params['count'], $params['period']);
        }
    }
}