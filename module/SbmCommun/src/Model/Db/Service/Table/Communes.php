<?php
/**
 * Gestion de la table `communes`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Communes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 06 jan. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Table;

class Communes extends AbstractSbmTable implements EffectifInterface
{

    /**
     * Initialisation de la commune
     */
    protected function init()
    {
        $this->table_name = 'communes';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Communes';
        $this->id_name = 'communeId';
    }

    /**
     * Renvoie l'identifiant d'une commune desservie dont on donne le nom ou l'alias ou
     * l'alias_laposte
     *
     * @param string $nom
     *
     * @return string Code INSEE de la commune
     */
    public function getCommuneId($nom)
    {
        $result = $this->fetchAll([
            'nom' => $nom,
            'desservie' => 1
        ]);
        if (! is_null($result)) {
            return $result->current()->communeId;
        } else {
            $result = $this->fetchAll([
                'alias' => $nom,
                'desservie' => 1
            ]);
            if (! is_null($result)) {
                return $result->current()->communeId;
            } else {
                $result = $this->fetchAll([
                    'alias_laposte' => $nom,
                    'desservie' => 1
                ]);
                if (! is_null($result)) {
                    return $result->current()->communeId;
                } else {
                    return null;
                }
            }
        }
    }

    public function getCodePostal($communeId)
    {
        if (! empty($communeId)) {
            try {
                $c = $this->getRecord($communeId);
                return $c->codePostal;
            } catch (Exception\ExceptionInterface $e) {
                // $communeId n'a pas été trouvée
                return '';
            }
        } else {
            return '';
        }
    }

    public function setVisible($communeId)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray([
            'communeId' => $communeId,
            'visible' => 1
        ]);
        $this->saveRecord($oData);
    }

    public function setSelection($communeId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray([
            'communeId' => $communeId,
            'selection' => $selection
        ]);
        parent::saveRecord($oData);
    }

    /**
     * Renvoie la liste des communes membres dans l'ordre alphabétique
     *
     * @return array
     */
    public function getListeMembre()
    {
        $liste = [];
        foreach ($this->fetchAll([
            'membre' => 1
        ], 'nom') as $commune) {
            $liste[] = $commune->alias;
        }
        return $liste;
    }

    /**
     * Renvoie la liste des communes desservies dans l'ordre alphabétique
     *
     * @return array
     */
    public function getListeDesservie()
    {
        $liste = [];
        foreach ($this->fetchAll([
            'desservie' => 1
        ], 'nom') as $commune) {
            $liste[] = $commune->alias;
        }
        return $liste;
    }

    /**
     * Renvoie la liste des communes visibles dans l'ordre alphabétique
     *
     * @return array
     */
    public function getListeVisible()
    {
        $liste = [];
        foreach ($this->fetchAll([
            'visible' => 1
        ], 'nom') as $commune) {
            $liste[] = $commune->alias;
        }
        return $liste;
    }

    /**
     * Renvoie la liste des communes avec inscription en ligne autorisée dans l'ordre
     * alphabétique
     *
     * @return array
     */
    public function getListeInscriptionEnLigne()
    {
        $liste = [];
        foreach ($this->fetchAll([
            'inscriptionenligne' => 1
        ], 'nom') as $commune) {
            $liste[] = $commune->alias;
        }
        return $liste;
    }

    /**
     * Renvoie la liste des communes avec paiement en ligne autorisé dans l'ordre
     * alphabétique
     *
     * @return array
     */
    public function getListePaiementEnLigne()
    {
        $liste = [];
        foreach ($this->fetchAll([
            'paiementenligne' => 1
        ], 'nom') as $commune) {
            $liste[] = $commune->alias;
        }
        return $liste;
    }
}

