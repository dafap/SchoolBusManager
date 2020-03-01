<?php
/**
 * Méthodes nécessaires à l'envoi de Sms, en lien avec la structure du module
 * et l'accès aux données
 *
 * @project sbm
 * @package SbmGestion/Model/Communication
 * @filesource Telephones.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 fév. 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Model\Communication;

class Telephones extends AbstractObject
{

    private $aQueryMethods;

    public function __construct(array $post)
    {
        parent::__construct($post);
        $this->aQueryMethods = [
            'circuit' => 'getResponsablesPointCircuit',
            'classe' => 'getResponsablesClasse',
            'commune' => 'getResponsablesCommune',
            'etablissement' => 'getResponsablesEtablissement',
            'ligne' => 'getResponsablesLigne',
            'lot' => 'getResponsablesLot',
            'organisme' => 'getResponsablesOrganisme',
            'responsable' => 'getResponsablesSelectionnes',
            'service' => 'getResponsablesService',
            'station' => 'getResponsableStation',
            'tarif' => 'getResponsablesGrilleTarif',
            'transporteur' => 'getResponsablesTransporteur'
        ];
    }

    public function getQueryMethod()
    {
        return $this->aQueryMethods[$this->filterName];
    }

    public function getQueryParam()
    {
        return $this->filterValue;
    }

    public function setQueryParams($id)
    {
        $this->filterValue = $id;
    }
}