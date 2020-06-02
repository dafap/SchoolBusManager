<?php
/**
 * Injection des paramètres dans la classe ApiSms
 *
 * @project sbm
 * @package SbmEsendex/src/Model
 * @filesource ApiSmsFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex\Model;

use Esendex\Authentication\LoginAuthentication;
use SbmBase\Model\StdLib;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ApiSmsFactory implements FactoryInterface, ApiSmsInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config_application = $serviceLocator->get('config');
        $config_service_sms = StdLib::getParamR([
            'sbm',
            'servicesms'
        ], $config_application, []);
        if (! $this->isvalid($config_service_sms)) {
            throw new OutOfBoundsException('Mauvaise configuration du module SbmEsendex');
        }
        $authentication = new LoginAuthentication($config_service_sms['account'],
            $config_service_sms['username'], $config_service_sms['password']);
        return new ApiSms($authentication, $config_service_sms['api_url'],
            $config_service_sms['path_filelog'], $config_service_sms['filename'],
            $config_service_sms['originatorId']);
    }

    private function isvalid(array $config)
    {
        $valid = true;
        foreach ([
            'api_url',
            'path_filelog',
            'filename',
            'username',
            'password',
            'account',
            'originatorId'
        ] as $key) {
            $value = trim(Stdlib::getParam($key, $config));
            $valid &= ! empty($value);
        }
        return $valid;
    }
}