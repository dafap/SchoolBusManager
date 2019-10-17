<?php
/**
 * Injection de la configuration dans CurlRequest
 *
 * @project sbm
 * @package SbmCleverSms/src/Model
 * @filesource CurlRequestFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 oct. 2019
 * @version 2019-4.5.2
 */
namespace SbmCleverSms\Model;

use SbmBase\Model\StdLib;
use SbmCleverSms\Model\Exception\OutOfBoundsException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CurlRequestFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config_application = $serviceLocator->get('config');
        $config_clever_sms = StdLib::getParamR([
            'sbm',
            'servicesms'
        ], $config_application, []);
        if (! $this->isvalid($config_clever_sms)) {
            throw new OutOfBoundsException(
                'Mauvaise configuration du module SbmCleverSms');
        }
        return new CurlRequest($config_clever_sms);
    }

    private function isvalid(array $config)
    {
        $valid = true;
        foreach ([
            'api_url',
            'path_filelog',
            'filename',
            'username',
            'password'
        ] as $key) {
            $value = trim(Stdlib::getParam($key, $config));
            $valid &= ! empty($value);
        }
        return $valid;
    }
}