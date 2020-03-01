<?php
/**
 * Objet de base servant aux classes de ce dossier
 *
 * @project sbm
 * @package SbmGestion/Model/Communication
 * @filesource AbstractObject.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 fév. 2020
 * @version 2020-2.6.0
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
            'ligne' => '/gestion/transport',
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

    /**
     * Traitement particulier pour service.
     *
     * @param array $post
     */
    private function setFilter(array $post)
    {
        if ($this->validAndSetServiceKeys($post))
            return;
        // cas général
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
     * Si $data contient les clés contenues dans $ref alors c'est un service, on
     * initialise les propriétés filterNae et filterValue et on renvoie true
     *
     * @param array $data
     * @return boolean
     */
    private function validAndSetServiceKeys(array $data)
    {
        $ref = [
            'ligneId',
            'sens',
            'moment',
            'ordre'
        ];
        $ok = array_values(array_intersect(array_keys($data), $ref)) == $ref;
        if ($ok) {
            $this->filterName = 'service';
            $this->filterValue = array_intersect_key($data, array_combine($ref, $ref));
        }
        return $ok;
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
