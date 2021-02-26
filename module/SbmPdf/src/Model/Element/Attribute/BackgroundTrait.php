<?php
/**
 * Méthodes set pour un BackgroundAttribute
 *
 * @project sbm
 * @package SbmPdf/src/Model/Element/Attribute
 * @filesource BackgroundTrait.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 janv. 2021
 * @version 2021-2.6.1
 */
namespace SbmPdf\Model\Element\Attribute;

trait BackgroundTrait
{

    /**
     *
     * @param string|array $backgroundColor
     * @return self
     */
    public function setBackgroundColor($backgroundColor): self
    {
        $this->getBackground()->setColor($backgroundColor);
        return $this;
    }

    /**
     *
     * @param mixed $dpi
     * @return self
     */
    public function setBackgroundDpi($dpi): self
    {
        $this->getBackground()->setDpi($dpi);
        return $this;
    }

    /**
     *
     * @param string|\SplFileInfo $backgroundImage
     * @return self
     */
    public function setBackgroundImage($backgroundImage): self
    {
        $this->getBackground()->setImage($backgroundImage);
        return $this;
    }

    /**
     *
     * @param callable $formatter
     * @return self
     */
    public function setBackgroundFormatter(callable $formatter = null): self
    {
        $this->getBackground()->setFormatter($formatter);
        return $this;
    }
}