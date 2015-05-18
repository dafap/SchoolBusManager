<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource Template.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 mai 2015
 * @version 2015-1
 */
namespace DafapMail\Model;

use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Zend\View\Model\ViewModel;

class Template
{
    /**
     * 
     * @var \Zend\View\Renderer\PhpRenderer
     */
    private $renderer;
    
    /**
     * Nom du template à utiliser
     * 
     * @var string
     */
    private $template;

    public function __construct($template)
    {
        $this->template = $template;
        $this->renderer = new PhpRenderer();
        $resolver = new Resolver\AggregateResolver();
        $this->renderer->setResolver($resolver);
        $map = new Resolver\TemplateMapResolver(array(
            'layout' => __DIR__ . '/../../../templates/layout.phtml',
            $template => __DIR__ . "/../../../templates/$template.phtml"
        ));
        $resolver->attach($map);
            //->attach(new Resolver\RelativeFallbackResolver($map));
    }

    public function render(array $data)
    {
        $model = new ViewModel($data);
        $model->setTemplate($this->template);
        $content = $this->renderer->render($model);
        $model = new ViewModel(array('content' => $content));
        $model->setTemplate('layout');
        return $this->renderer->render($model);
    }
}