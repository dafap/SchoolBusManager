<?php
/**
 * Description de l'attribut background d'un élément
 *
 * @project sbm
 * @package SbmPdf/src/Model/Element/Attribute
 * @filesource BackgroundAttribute.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 janv. 2021
 * @version 2021-2.6.1
 */
namespace SbmPdf\Model\Element\Attribute;

/**
 *
 * @author alain from naitsirch (naitsirch@e.mail.de)
 * @see https://github.com/naitsirch/tcpdf-extension
 */
class BackgroundAttribute extends AbstractAttribute
{

    /**
     * Code de couleur RGB soit sous forme de chaine, soit sous tableau de valeurs
     * décimales, ou mot 'transparent' ou <b>null</b>
     *
     * @var string|array|null
     */
    private $color;

    /**
     * Peut être le nom d'un fichier image (avec son chemin absolu) ou le contenu bianire
     * de l'image ou un objet \SplFileInfo.
     *
     * @var string|\SplFileInfo[null
     */
    private $image;

    /**
     *
     * @var number|null
     */
    private $dpi;

    /**
     *
     * @var callable|null
     */
    private $formatter;

    /**
     *
     * @return string|array|NULL
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Get the background image.
     *
     * @return null|array <pre>null or array(
     *         'image' => nom du fichier image, binaire de l'image ou objet \SplFileInfo
     *         'info' => array()
     *         )</pre>
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     *
     * @return number|NULL
     */
    public function getDpi()
    {
        return $this->dpi;
    }

    /**
     *
     * @return callable|NULL
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * Set the background color.
     *
     * $cell->setBackgroundColor('#ffffff'); // hexadecimal CSS notation
     * $cell->setBackgroundColor([255, 255, 255]);
     * $cell->setBackgroundColor(null); // this means transparent
     *
     * @param string|array|null $backgroundColor
     * @return self
     */
    public function setColor($color)
    {
        $this->color = $color;
        return $this;
    }

    /**
     * Set the background image.
     *
     * @param string|\SplFileInfo $backgroundImage
     *            Absolute filename of the image, binary file content or \SplFileInfo
     *            object
     * @param array $info
     * @return self
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     *
     * @param number $dpi
     * @return \SbmPdf\Model\Element\Attribute\BackgroundAttribute
     */
    public function setDpi($dpi)
    {
        $this->dpi = $dpi;
        return $this;
    }

    /**
     * Set a formatter callable for the background.
     * This allows you to
     * modify options of the image on the run.
     *
     * @param callable $formatter
     * @return self
     */
    public function setFormatter($formatter)
    {
        $this->formatter = $formatter;
        return $this;
    }
}