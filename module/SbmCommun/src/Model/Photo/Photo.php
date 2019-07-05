<?php
/**
 * Outils de retouche et de normalisation des photos d'identité
 *
 * La photo d'identité est ramenée à une résolution de 150 ou 300 dpi.
 * On détecte si une bordure blanche ou noire est présente et on la supprime
 * (ce qui règle le pb d'une photo scannée dans page A4).
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
 * @date 5 juil. 2019
 * @version 2019-2.4.6
 */
namespace SbmCommun\Model\Photo;

use Zend\Form;

class Photo
{

    const COLOR_WHITE = 0xa0a0a0;

    const COLOR_BLACK = 0x1f1f1f;

    protected $resolution;

    protected $quality;

    protected $rapport;

    /**
     * Chaine représentant une photo (fournie par la méthode getImageJpegAsString)
     *
     * @var string
     */
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
            ->setLabelAttributes([
            'class' => 'right-10px'
        ])
            ->setOption('error_attributes', [
            'class' => 'sbm-error'
        ]);
        $this->form->add($file_element);
        $this->form->add(new Form\Element\Hidden('eleveId'));
        $this->form->add(new Form\Element\Button('envoiphoto'));
        // $this->form->add(new Form\Element\Button('supprphoto'));
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
        $messageCallback = function ($item) use (&$messagesToPrint, $translator,
        $textDomain) {
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
     * Taille de l'image en px selon la résolution. Cette classe ne traite que 150 (par
     * défaut) ou 300 dpi
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
     * Renvoie un flux binaire (blob) du codage de l'image en jpeg. L'image est
     * reconstruite pour être à la taille correspondante à une photo d'identité de 35 x 45
     * mm, à la résolution initialisée, de type JPEG. Par défaut, la résolution est de 150
     * dpi. Attention ! Pour l'enregistrer en base de données, penser à l'échapper par
     * addslashes()
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
                sprintf("Le fichier source n'est pas reconnu comme un fichier image. %s",
                    $mime_type));
        }
        if ($mime_type == 'image/jpeg') {
            $image = imagecreatefromjpeg($source);
        } elseif ($mime_type == 'image/gif') {
            $image = imagecreatefromgif($source);
        } elseif ($mime_type == 'image/png') {
            $image = imagecreatefrompng($source);
        } else {
            unlink($source);
            throw new Exception(
                "Ce format image n'est pas accepté (JPEG, PNG ou GIF uniquement).");
        }
        // supprime les cadres blancs ou noirs éventuels
        $image = $this->supprimeCadre($image);
        // mise à la bonne taille de l'image, rognage si nécessaire, résultat dans la
        // propriété $photo de cet objet
        return $this->getStringFromImage($this->normaliseProportions($image));
    }

    private function getStringFromImage($image)
    {
        ob_start();
        imagejpeg($image, null, $this->quality);
        return ob_get_clean();
    }

    /**
     * Mise de l'image aux bonnes proportions. Rognage si nécessaire. La photo est centrée
     * horizontalement et prise à partir du haut verticalement.
     *
     * @param resource $image
     * @return resource
     */
    private function normaliseProportions($image)
    {
        list ($modwidth, $modheight) = $this->modele_size();
        $info = [
            imagesx($image),
            imagesy($image)
        ]; // getimagesize($source);
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
        return $photo;
    }

    /**
     * Supprime un cadre blanc, noir, gris, contenant la photo. Utile lorsque la photo a
     * une bordure blanche due à un mauvais scan (page A4 par exemple).
     *
     * @param resource $im
     *
     * @return resource
     */
    private function supprimeCadre($im)
    {
        $border = 6;
        // on va étudier l'image à l'intérieur d'un cadre en commençant par un cadre de 6
        // de largeur et en le diminuant progressivement si on n'en a pas trouvé.
        $b_top = $border - 1;
        $b_btm = imagesy($im) - $border;
        $b_lft = $border - 1;
        $b_rt = imagesx($im) - $border;
        // top
        for ($t = $b_top; $t < $b_btm; ++ $t) {
            for ($x = $b_lft; $x < $b_rt; ++ $x) {
                $color = imagecolorat($im, $x, $t);
                if ($color <= self::COLOR_WHITE && $color >= self::COLOR_BLACK) {
                    break 2; // sortie des 2 boucles
                }
            }
        }
        if ($t == $b_top) {
            for ($t1 = $t - 1; $t1 >= 0 && $t1 < $t; -- $t) {
                for ($x = $b_lft; $x < $b_rt; ++ $x) {
                    $color = imagecolorat($im, $x, $t1);
                    if ($color <= self::COLOR_WHITE && $color >= self::COLOR_BLACK) {
                        -- $t1;
                        break; // sortie de la seconde boucle
                    }
                }
            }
            $t = $t1 + 1;
        }
        // bottom
        $b_btm = imagesy($im) - $border;
        for ($b = $b_btm; $b > $t; -- $b) {
            for ($x = $b_lft; $x < $b_rt; ++ $x) {
                $color = imagecolorat($im, $x, $b);
                if ($color <= self::COLOR_WHITE && $color >= self::COLOR_BLACK) {
                    ++ $b_btm;
                    break 2; // sortie des 2 boucles
                }
            }
        }
        if ($b == $b_btm) {
            for ($b1 = $b + 1; $b1 <= imagesy($im) && $b1 > $b; ++ $b) {
                for ($x = $b_lft; $x < $b_rt; ++ $x) {
                    $color = imagecolorat($im, $x, $b1);
                    if ($color <= self::COLOR_WHITE && $color >= self::COLOR_BLACK) {
                        ++ $b1;
                        break; // sortie de la seconde boucle
                    }
                }
            }
            $b = $b1 - 1;
        }
        // left
        for ($l = $b_lft; $l < $b_rt; ++ $l) {
            for ($y = $t; $y < $b; ++ $y) {
                $color = imagecolorat($im, $l, $y);
                if ($color <= self::COLOR_WHITE && $color >= self::COLOR_BLACK) {
                    break 2; // sortie des 2 boucles
                }
            }
        }
        if ($l == $b_lft) {
            for ($l1 = $l - 1; $l1 >= 0 && $l1 < $l; -- $l) {
                for ($y = $t; $y < $b; ++ $y) {
                    $color = imagecolorat($im, $l1, $y);
                    if ($color <= self::COLOR_WHITE && $color >= self::COLOR_BLACK) {
                        -- $l1;
                        break; // sortie de la seconde boucle
                    }
                }
            }
            $l = $l1 + 1;
        }
        // right
        for ($r = $b_rt; $r > $l; -- $r) {
            for ($y = $t; $y < $b; ++ $y) {
                $color = imagecolorat($im, $r, $y);
                if ($color <= self::COLOR_WHITE && $color >= self::COLOR_BLACK) {
                    ++ $b_rt;
                    break 2; // sortie des 2 boucles
                }
            }
        }
        if ($r == $b_rt) {
            for ($r1 = $r + 1; $r1 <= imagesx($im) && $r1 > $r; ++ $r) {
                for ($y = $t; $y < $b; ++ $y) {
                    $color = imagecolorat($im, $r1, $y);
                    if ($color <= self::COLOR_WHITE && $color >= self::COLOR_BLACK) {
                        ++ $r1;
                        break; // sortie de la seconde boucle
                    }
                }
            }
            $b = $b1 - 1;
        }

        // Renvoie la partie copiée si succès, sinon l'image d'origine
        $newim = imagecreatetruecolor($r - $l, $b - $t);
        if (imagecopy($newim, $im, 0, 0, $l, $t, imagesx($newim), imagesy($newim))) {
            return $newim;
        }
        return $im;
    }

    /**
     * Renvoie la chaine src de la balise <img src="... Cette chaine est de la forme
     * data:image/jpeg;base64,... (jpeg peut être remplacé par un autre type)
     *
     * @param string $imagebinary
     * @param string $imagetype
     *            'jpeg' ou 'gif' ou 'png'
     * @return string
     */
    public function img_src($imagebinary, $imagetype = 'jpeg')
    {
        return 'data:image/' . $imagetype . ';base64,' . base64_encode($imagebinary);
    }

    /**
     * Renvoie une image grise avec une croix rouge et le message 'Pas de photo' de type
     * gif.
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

    /**
     * Reçoit une photo sous forme de chaine binaire et renvoie la photo (chaine binaire)
     * transformée par rotation.
     *
     * @param string $photo
     * @param float $degres
     * @return string
     */
    public function rotate(string $photo, float $degres)
    {
        $im = imagerotate(imagecreatefromstring($photo), $degres, 0);
        $im = $this->supprimeCadre($im);
        return $this->getStringFromImage($this->normaliseProportions($im));
    }
}