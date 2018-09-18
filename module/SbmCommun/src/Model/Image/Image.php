<?php
/**
 * Entité Image
 *
 * Contient une image ou le nom de son fichier ainsi des paramètres et des méthodes de transformation.
 * 
 * @project sbm
 * @package SbmCommun\Model\Image
 * @filesource Image.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Model\Image;

class Image
{

    const GIF = IMAGETYPE_GIF;

    const JPG = IMAGETYPE_JPEG;

    const PNG = IMAGETYPE_PNG;

    const FULL_SIZE = 'real';

    const FIXED_SIZE = 'fixe';

    const PROPORTIONAL_SIZE = 'scale';

    protected $dpi = 72;

    protected $file_name;

    protected $height;
    
    protected $image;

    protected $mime;

    protected $taille;

    protected $type;

    protected $width;

    public function __construct($width = 0, $height = 0, $size = self::FULL_SIZE, $type = self::PNG)
    {
        $this->width = $width;
        $this->height = $height;
        $this->taille = $size;
        $this->setType($type);
    }

    public function setDpi($dpi)
    {
        $this->dpi = $dpi;
    }

    public function setFileName($file_name)
    {
        $this->file_name = $file_name;
    }

    public function setType($type)
    {
        $this->type = $type;
        $this->mime = image_type_to_mime_type($type);
    }

    public function createImage($file_name)
    {
        
    }
    public function resize()
    {
        switch ($this->taille) {
            case self::FULL_SIZE:
                return;
                break;
            case self::FIXED_SIZE:
                break;
            default:
                ;
                break;
        }
        ;
    }
}