<?php
/**
 * Réponse pour AbstractActionController::editData ou AbstractActionController::addData
 *
 * @project sbm
 * @package SbmCommun/Model/Mvc/Controller
 * @filesource EditResponse.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 mars 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Mvc\Controller;

class EditResponse
{
    private $post;
    private $result;
    private $status;
    public function __construct($status, $post, $result = null)
    {
        $this->post = $post;
        $this->result = $result;
        $this->status = $status;
    }
    public function getStatus()
    {
        return $this->status;
    }
    public function getPost()
    {
        return $this->post;
    }
    public function getResult()
    {
        return $this->result;
    }
}