<?php
/**
 * Renvoie du code html à partir d'un template, d'un layout et de datas
 * 
 * Si le template n'est pas null, il contient un texte avec des variables qui doivent
 * être passées par le tableau associatif $data de la méthode render()
 * Si le template est null, le tableau associatif $data est de la forme 'body' => contenu html
 * 
 * Le template sert à définir la signature, les logos, les images ...
 * Si un template est donné, il est appliqué, sinon c'est le template par défaut qui le sera.
 *
 * @project sbm
 * @package SbmMail/Model
 * @filesource Template.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 août 2016
 * @version 2016-2.2.0
 */
namespace SbmMail\Model;

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
    
    /**
     * Tableau de variables ['key' => 'value', ...] pour le layout
     * 
     * @var array
     */
    private $vars;

    public function __construct($template = null, $layout = 'layout', $vars = [])
    {
        $this->vars = $vars;
        $this->template = $template;
        $this->renderer = new PhpRenderer();
        $resolver = new Resolver\AggregateResolver();
        $this->renderer->setResolver($resolver);
        $templates = array(
            'layout' => __DIR__ . "/../../../templates/$layout.phtml"
        );
        if (! empty($template)) {
            $templates[$template] = __DIR__ . "/../../../templates/$template.phtml";
        }
        $map = new Resolver\TemplateMapResolver($templates);
        $resolver->attach($map);
        // ->attach(new Resolver\RelativeFallbackResolver($map));
    }

    /**
     * Renvoie du code html
     * 
     * @param array $data
     *            tableau des variables à passer au 'template' 
     *            ou array('body' => contenu) si le 'template' est null
     * @return \Zend\View\Renderer\string
     */
    public function render(array $data)
    {
        if (empty($this->template)) {
            $content = $data['body'];
        } else {
            $model = new ViewModel($data);
            $model->setTemplate($this->template);
            $content = $this->renderer->render($model);
        }
        $model = new ViewModel(array(
            'content' => $content
        ));
        $model->setTemplate('layout');
        foreach ($this->vars as $key => $value) {
            $model->setVariable($key, $value);
        }
        return $this->renderer->render($model);
    }
}