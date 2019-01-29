<?php
/**
 * Outis de retouche et de normalisation des photos d'identité
 * 
 * La photo d'identité est ramenée à une résolution de 150 ou 300 dpi.
 * La photo d'identité est ramenée à la taille 3,5 x 4,5 cm par un découpage
 * - si la photo est trop large, le rognage est centré
 * - si la photo est trop haute, le rognage est en bas
 * La photo d'identité est produite au format final JPEG mais le fichier d'origine
 * peut être du type JPEG, GIF ou PNG
 * 
 * @project sbm
 * @package SbmCommun/Model/Photo
 * @filesource Photo.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 jan. 2019
 * @version 2019-2.4.6
 */
namespace SbmCommun\Model\Photo;

use Zend\Form;

class Photo
{

    protected $resolution;

    protected $quality;

    protected $rapport;

    private $photo;
    
    private $form = null;

    public function __construct($quality = 70, $resolution = 150)
    {
        $this->quality = $quality;
        $this->resolution = $resolution;
        $this->rapport = 35 / 45; // largeur / hauteur en mm
    }

    /**
     *
     * @param int $resolution
     *            cette classe ne traite que 150 (par défaut) ou 300 dpi
     *            
     * @return \SbmCommun\Model\Photo\Photo
     */
    public function setResolution($resolution)
    {
        $this->resolution = $resolution;
        return $this;
    }

    /**
     *
     * @param int $quality
     *            de 0 à 100
     *            
     * @return \SbmCommun\Model\Photo\Photo
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;
        return $this;
    }

    /**
     * initialise la propriété form
     */
    public function iniForm()
    {
        $this->form = new Form\Form('formphoto');
        $this->form->setAttribute('method', 'post');
         
        $file_element = new Form\Element\File('filephoto');
        $file_element->setLabel('Choisissez le fichier image (JPEG, PNG ou GIF)')
        ->setAttribute('id', 'filephoto')
        ->setLabelAttributes(['class' => 'right-10px'])
        ->setOption('error_attributes',
            [
                'class' => 'sbm-error'
            ]);
        $this->form->add($file_element);
        $this->form->add(new Form\Element\Hidden('eleveId'));
        $this->form->add(new Form\Element\Button('envoiphoto'));
        //$this->form->add(new Form\Element\Button('supprphoto'));
    }
    
    /**
     * Renvoie la propriété
     * 
     * @return \Zend\Form\Form
     */
    public function getForm()
    {        
        if (! $this->form) {
            $this->iniForm();
        }
        return $this->form;
    }
    
    public function getFormWithInputFilter($tmpuploads)
    {
        $this->getForm();
        $fi = new \Zend\InputFilter\FileInput('filephoto');
        $fi->getValidatorChain()->attach(new \Zend\Validator\File\UploadFile());
        $fi->getFilterChain()->attach(
            new \Zend\Filter\File\RenameUpload(
                [
                    'target' => $tmpuploads,
                    'randomize' => true,
                    'use_upload_extension' => true
                ]));
        $this->form->getInputFilter()->add($fi);
        return $this->form;
    }
    
    /**
     * Renvoie un tableau de messages d'erreurs traduits dans la langue par défaut
     * 
     * @return array(string)
     */
    public function getMessagesFilePhotoElement()
    {
        $messages = $this->form->getMessages('filephoto');
        $translator = \Zend\Validator\AbstractValidator::getDefaultTranslator();
        $textDomain = \Zend\Validator\AbstractValidator::getDefaultTranslatorTextDomain();
        $messagesToPrint = [];
        $messageCallback = function ($item) use (&$messagesToPrint, $translator, $textDomain) {
            $msg = $translator->translate($item, $textDomain);
            if ($msg == 'Une valeur est requise et ne peut être vide') {
                $msg = 'Le fichier n\'a pas été transmis (trop gros).';
            }
            $messagesToPrint[] = $msg;
        };
        array_walk_recursive($messages, $messageCallback);
        return $messagesToPrint;
    }  
      
    /**
     * Taille de l'image en px selon la résolution.
     * Cette classe ne traite que 150 (par défaut) ou 300 dpi
     *
     * @param number $resolution            
     *
     * @return array[integer];
     */
    private function modele_size($resolution = 150)
    {
        if (! $resolution) {
            $resolution = $this->resolution;
        }
        if ($resolution == 300) {
            return [
                413,
                531
            ];
        } else {
            // resolution par défaut (150)
            return [
                207,
                266
            ];
        }
    }

    /**
     * Renvoie un flux binaire (blob) du codage de l'image en jpeg.
     * L'image est reconstruite pour être à la taille correspondante à une photo
     * d'identité de 35 x 45 mm, à la résolution initialisée, de type JPEG.
     * Par défaut, la résolution est de 150 dpi.
     *
     * Attention ! Pour l'enregistrer en base de données, penser à l'échapper 
     * par addslashes()
     *
     * @param string $source            
     *
     * @throws Exception
     *
     * @return string
     */
    public function getImageJpegAsString($source)
    {
        if (! file_exists($source)) {
            throw new Exception("Le fichier source $source n'existe pas.");
        }
        $mime_type = mime_content_type($source);
        if (substr($mime_type, 0, strlen('image/')) != 'image/') {
            unlink($source);
            throw new Exception(
                sprintf("Le fichier source n'est pas reconnu comme un fichier image. %s", $mime_type));
        }
        if ($mime_type == 'image/jpeg') {
            $image = imagecreatefromjpeg($source);
        } elseif ($mime_type == 'image/gif') {
            $image = imagecreatefromgif($source);
        } elseif ($mime_type == 'image/png') {
            $image = imagecreatefrompng($source);
        } else {
            unlink($source);
            throw new Exception("Ce format image n'est pas accepté (JPEG, PNG ou GIF uniquement).");
        }
        // mise à la bonne taille de l'image, rognage si nécessaire
        list ($modwidth, $modheight) = $this->modele_size();
        $info = getimagesize($source);
        $rapportSource = $info[0] / $info[1];
        if ($rapportSource > $this->rapport) {
            // image d'origine trop large : on rogne à gauche et à droite
            $src_w = (int) ($info[1] * $this->rapport);
            $src_h = $info[1];
            $src_x = ($info[0] - $src_w) / 2;
        } elseif ($rapportSource < $this->rapport) {
            // image d'origine trop étroite : on rogne en bas
            $src_x = 0;
            $src_w = $info[0];
            $src_h = (int) ($info[0] / $this->rapport);
        } else {
            $src_x = 0;
            $src_w = $info[0];
            $src_h = $info[1];
        }
        $photo = imagecreatetruecolor($modwidth, $modheight);
        imagecopyresampled($photo, $image, 0, 0, $src_x, 0, $modwidth, $modheight, $src_w, 
            $src_h);
        imagedestroy($image);
        ob_start();
        imagejpeg($photo, null, $this->quality);
        return ob_get_clean();
    }

    /**
     * Renvoie la chaine src de la balise <img src="...
     * Cette chaine est de la forme data:image/jpeg;base64,...
     * (jpeg peut être remplacé par un autre type)
     *
     * @param string $imagebinary            
     * @param string $imagetype
     *            'jpeg' ou 'gif' ou 'png'
     *            
     * @return string
     */
    public function img_src($imagebinary, $imagetype = 'jpeg')
    {
        return 'data:image/' . $imagetype . ';base64,' . base64_encode($imagebinary);
    }

    /**
     * Renvoie une image grise avec une croix rouge et le message 'Pas de photo'
     * de type gif.
     *
     * @return string
     */
    public function getSansPhotoGifAsString()
    {
        list ($modwidth, $modheight) = $this->modele_size();
        $image = imagecreatetruecolor($modwidth, $modheight);
        $bgcolor = imagecolorallocate($image, 245, 245, 245);
        $red = imagecolorallocate($image, 255, 0, 0);
        imagefill($image, 0, 0, $bgcolor);
        $fw = imagefontwidth(5);
        imagestring($image, 5, $modwidth / 2 - 6 * $fw, 
            $modheight / 2 - imagefontheight(5) / 2, 'Pas de photo', $red);
        imageline($image, 5, 5, $modwidth - 5, $modheight - 5, $red);
        imageline($image, 5, $modheight - 5, $modwidth - 5, 5, $red);
        ob_start();
        imagegif($image);
        return ob_get_clean();
    }

}