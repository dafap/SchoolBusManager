<?php
/**
 * Interface pour les classes Document
 *
 *
 * @project sbm
 * @package SbmPdf/src/Model/Document
 * @filesource DocumentInterface.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 fév. 2021
 * @version 2021-2.6.1
 */
namespace SbmPdf\Model\Document;

use Zend\Stdlib\Parameters;

interface DocumentInterface
{

    /**
     * La propriété 'config' est un tableau qui contient les paramètres lus dans les
     * tables décrivant le document.
     * Tous les documents possède une section 'document' dans 'config'
     *
     * @param string $key
     * @param array $config
     * @return self
     */
    public function setConfig(string $key, array $config): self;


    /**
     * Injecte le documentId
     *
     * @param int $documentId
     * @return self
     */
    public function setDocumentId(int $documentId): self;

    /**
     * Les paramètres sont transmis par le controleur qui appelle le document.
     *
     * @param \Zend\Stdlib\Parameters $params
     * @return self
     */
    public function setParams(Parameters $params): self;

    /**
     * Prépare et renvoie le pdf sous forme de chaine
     *
     * @return string
     */
    public function render(): string;
}

