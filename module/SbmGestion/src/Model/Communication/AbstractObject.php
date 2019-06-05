<?php
/**
 * Objet de base servant aux classes de ce dossier
 *
 * @project sbm
 * @package SbmGestion/Model/Communication
 * @filesource AbstractObject.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 05 juin 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Communication;

use SbmBase\Model\StdLib;

abstract class AbstractObject
{

    private $aOrigines;

    private $page;

    protected $filterName;

    protected $filterValue;

    /**
     *
     * @param array $post
     */
    public function __construct(array $post)
    {
        $this->aOrigines = [
            'circuit' => '/gestion/transport',
            'classe' => '/gestion/transport',
            'commune' => '/gestion/transport',
            'etablissement' => '/gestion/transport',
            'lot' => '/gestion/transport',
            'organisme' => '/gestion/finance',
            'responsable' => '/gestion/eleve',
            'service' => '/gestion/transport',
            'station' => '/gestion/transport',
            'tarif' => '/gestion/finance',
            'transporteur' => '/gestion/transport'
        ];
        $this->setFilter($post);
        $this->page = StdLib::getParam('page', $post, 1);
    }

    public function getFilterName()
    {
        return $this->filterName;
    }

    public function getFilterValue()
    {
        return $this->filterValue;
    }

    private function setFilter(array $post)
    {
        foreach (array_keys($this->aOrigines) as $key) {
            if (array_key_exists($key . 'Id', $post)) {
                $this->filterName = $key;
                $this->filterValue = $post[$key . 'Id'];
                return;
            }
        }
        $this->filterName = 'responsable';
    }

    /**
     * Renvoie une chaine permettant d'initialiser la méthode
     * redirectToOrigin()->setBack()
     *
     * @param array $post
     *
     * @return string
     */
    public function getUrlBack(): string
    {
        return sprintf('%s/%s-liste/page/%d', $this->aOrigines[$this->filterName],
            $this->filterName, $this->page);
    }
}
