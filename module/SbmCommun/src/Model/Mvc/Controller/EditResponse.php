<?php
/**
 * Réponse pour AbstractActionController::editData ou AbstractActionController::addData
 *
 * @project sbm
 * @package SbmCommun/Model/Mvc/Controller
 * @filesource EditResponse.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
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