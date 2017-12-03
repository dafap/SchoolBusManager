<?php
/**
 * Formulaire de saisie des paramètres d'un document pdf
 * Définition des formats de page
 * (utilisation de jquery accordion pour l'affichage]
 *
 * @project sbm
 * @package SbmPdf/Form
 * @filesource DocumentPdf.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr]
 * @date 3 déc. 2017
 * @version 2017-2.3.14
 */
namespace SbmPdf\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use SbmCommun\Model\Strategy\Color;
use SbmPdf\Model\TcpdfFonts;
use SbmPdf\Model\Tcpdf;
use SbmBase\Model\StdLib;

class DocumentPdf extends Form implements InputFilterProviderInterface
{

    private $fonts;

    private $db_manager;
    private $auth_userId;
    private $template_method_list;

    public function __construct($db_manager, $auth_userId, $template_method_list)
    {
        $this->db_manager = $db_manager;
        $this->auth_userId = $auth_userId;
        $this->template_method_list = $template_method_list;
        $fonts = new TcpdfFonts();
        parent::__construct('documentpdf');
        $this->setAttribute('method', 'post');
        $this->add([
            'name' => 'documentId',
            'type' => 'hidden',
            'attributes' => [
                'id' => 'documentId'
            ]
        ]);
        $this->add([
            'name' => 'type',
            'type' => 'hidden'
        ]);
        $this->add([
            'name' => 'csrf',
            'type' => 'Zend\Form\Element\Csrf',
            'options' => [
                'csrf_options' => [
                    'timeout' => 180
                ]
            ]
        ]);
        
        $this->add([
            'name' => 'disposition',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'id' => 'documentpdf-disposition',
                'class' => 'sbm-width-35c'
            ],
            'options' => [
                'label' => 'Disposition',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'empty_option' => 'Choisissez la disposition',
                'value_options' => [
                    'Tabulaire' => 'Présentation tabulaire',
                    'Texte' => 'Page de texte',
                    'Etiquette' => 'Etiquettes'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'name',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-name',
                'class' => 'sbm-width-35c'
            ],
            'options' => [
                'label' => 'Nom du document pdf',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'out_mode',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'id' => 'documentpdf-out_mode',
                'class' => 'sbm-width-35c'
            ],
            'options' => [
                'label' => 'Récupération du pdf',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'value_options' => [
                    'I' => 'en ligne',
                    'D' => 'téléchargement',
                    'F' => 'fichier sur serveur',
                    'FI' => 'en ligne + fichier sur serveur',
                    'FD' => 'téléchargement + fichier sur serveur',
                    'S' => 'chaine de caractères',
                    'E' => 'intégration dans email'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'out_name',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-out_name',
                'class' => 'sbm-width-35c'
            ],
            'options' => [
                'label' => 'Nom du fichier pdf',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'value' => 'document.pdf',
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'recordSourceType',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'id' => 'documentpdf_recordSourceType',
                'class' => 'sbm-width-15c'
            ],
            'options' => [
                'label' => 'Provenance',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'empty_option' => 'Choisissez',
                'value_options' => [
                    'T' => 'table ou vue',
                    'R' => 'requête SQL'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'hidden',
            'name' => 'recordSource'
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Select',
            'name' => 'TrecordSource',
            'attributes' => [
                'id' => 'TrecordSource',
                'class' => 'sbm-width-35c'
            ],
            'options' => [
                'label' => 'Source des données',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'empty_option' => 'Choisissez une source de données',
                'disable_inarray_validator' => true,
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'RrecordSource',
            'attributes' => [
                'id' => 'RrecordSource',
                'class' => 'sbm-width-40c'
            ],
            'options' => [
                'label' => 'Requête',
                'label_attributes' => [
                    'class' => 'sbm-label-top'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'filter',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-filter',
                'class' => 'sbm-width-35c'
            ],
            'options' => [
                'label' => 'Filtre des données',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'orderBy',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-orderBy',
                'class' => 'sbm-width-35c'
            ],
            'options' => [
                'label' => 'Ordre de tri',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'url_path_images',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-url_path_images',
                'class' => 'sbm-width-30c'
            ],
            'options' => [
                'label' => 'Dossier contenant les images du document',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'image_blank',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-image_blank',
                'class' => 'sbm-width-30c'
            ],
            'options' => [
                'label' => 'Nom de l\'image vide',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'docheader',
            'attributes' => [
                'id' => 'documentpdf-docheader',
                'class' => 'sbm-checkbox'
            ],
            'options' => [
                'label' => 'Y a-t-il un en-tête de document ?   ',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'docfooter',
            'attributes' => [
                'id' => 'documentpdf-docfooter',
                'class' => 'sbm-checkbox'
            ],
            'options' => [
                'label' => 'Y a-t-il un pied de document ?',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'pageheader',
            'attributes' => [
                'id' => 'documentpdf-pageheader',
                'class' => 'sbm-checkbox'
            ],
            'options' => [
                'label' => 'Y a-t-il un en-tête de page ?',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'pagefooter',
            'attributes' => [
                'id' => 'documentpdf-pagefooter',
                'class' => 'sbm-checkbox'
            ],
            'options' => [
                'label' => 'Y a-t-il un pied de page ?',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'creator',
            'type' => 'hidden'
        ]);
        $this->add([
            'name' => 'author',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-author',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Auteur du document pdf',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'title',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-title',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Titre du document pdf',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'subject',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-subject',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Sujet du document pdf',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'keywords',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-keywords',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Mots clés',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'docheader_subtitle',
            'type' => 'Zend\Form\Element\Textarea',
            'attributes' => [
                'id' => 'documentpdf-docheader_subtitle',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Sous-titre du document pdf',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'docheader_page_distincte',
            'attributes' => [
                'id' => 'documentpdf-docheader_page_distincte',
                'class' => 'sbm-checkbox'
            ],
            'options' => [
                'label' => 'Saut de page après l\'en-tête de document ?',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'docheader_margin',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-docheader_margin',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Marge de l\'en-tête de document (en mm]',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'docheader_pageheader',
            'attributes' => [
                'id' => 'documentpdf-docheader_pageheader',
                'class' => 'sbm-checkbox'
            ],
            'options' => [
                'label' => 'En-tête de page sur la première page ?',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'docheader_pagefooter',
            'attributes' => [
                'id' => 'documentpdf-docheader_pagefooter',
                'class' => 'sbm-checkbox'
            ],
            'options' => [
                'label' => 'Pied de page sur la première page ? ',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'docheader_templateId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'id' => 'documentpdf-docheader_templateId',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Modèle de page d\'en-tête',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'empty_option' => 'Choisissez un modèle',
                'value_options' => $this->getTemplateList('docheader'),
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'docfooter_title',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-docfooter_title',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Titre du pied de document',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'docfooter_string',
            'type' => 'Zend\Form\Element\Textarea',
            'attributes' => [
                'id' => 'documentpdf-docfooter_string',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Texte du pied de document',
                'label_attributes' => [
                    'class' => 'sbm-label-top'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'docfooter_page_distincte',
            'attributes' => [
                'id' => 'documentpdf-docfooter_page_distincte',
                'class' => 'sbm-checkbox'
            ],
            'options' => [
                'label' => 'Saut de page avant le pied de document ?',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'docfooter_insecable',
            'attributes' => [
                'id' => 'documentpdf-docfooter_insecable',
                'class' => 'sbm-checkbox'
            ],
            'options' => [
                'label' => 'Pied de document insécable ?',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'docfooter_margin',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-docfooter_margin',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Marge du pied de document (en mm]',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'docfooter_pageheader',
            'attributes' => [
                'id' => 'documentpdf-docfooter_pageheader',
                'class' => 'sbm-checkbox'
            ],
            'options' => [
                'label' => 'En-tête de page sur la dernière page ?',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'docfooter_pagefooter',
            'attributes' => [
                'id' => 'documentpdf-docfooter_pagefooter',
                'class' => 'sbm-checkbox'
            ],
            'options' => [
                'label' => 'Pied de page sur la dernière page ?',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'docfooter_templateId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'id' => 'documentpdf-docfooter_templateId',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Modèle de pied de document',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'empty_option' => 'Choisissez un modèle',
                'value_options' => $this->getTemplateList('docfooter'),
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'pageheader_templateId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'id' => 'documentpdf-pageheader_templateId',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Modèle d\'en-tête de page',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'empty_option' => 'Choisissez un modèle',
                'value_options' => $this->getTemplateList('header'),
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'pageheader_title',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-pageheader_title',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Titre d\'en-tête de page',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'pageheader_string',
            'type' => 'Zend\Form\Element\Textarea',
            'attributes' => [
                'id' => 'documentpdf-pageheader_string',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Texte d\'en-tête de page',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'pageheader_logo_visible',
            'attributes' => [
                'id' => 'documentpdf-pageheader_logo_visible',
                'class' => 'sbm-checkbox'
            ],
            'options' => [
                'label' => 'Logo dans l\'en-tête de page ?',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'pageheader_logo',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-pageheader_logo',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Logo d\'en-tête de page',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'pageheader_logo_width',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-pageheader_logo_width',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Largeur du logo d\'en-tête de page (en mm]',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'pageheader_margin',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-pageheader_margin',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Marge de l\'en-tête de page',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'pageheader_font_family',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'id' => 'documentpdf-pageheader_font_family',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Police de l\'en-tête de page',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'empty_option' => 'Choisissez une police',
                'value_options' => $fonts->getFonts(),
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'pageheader_font_style',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-pageheader_font_style',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Style police de l\'en-tête de page',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'pageheader_font_size',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-pageheader_font_size',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Taille police de l\'en-tête de page',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'pageheader_text_color',
            'type' => 'Zend\Form\Element\Color',
            'attributes' => [
                'id' => 'documentpdf-pageheader_text_color',
                'class' => 'sbm-width-10c'
            ],
            'options' => [
                'label' => 'Couleur du texte dans l\'en-tête',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'pageheader_line_color',
            'type' => 'Zend\Form\Element\Color',
            'attributes' => [
                'id' => 'documentpdf-pageheader_line_color',
                'class' => 'sbm-width-10c'
            ],
            'options' => [
                'label' => 'Couleur des traits dans l\'en-tête',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'pagefooter_templateId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'id' => 'documentpdf-pagefooter_templateId',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Modèle de pied de page',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'empty_option' => 'Choisissez un modèle',
                'value_options' => $this->getTemplateList('footer'),
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'pagefooter_string',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-pagefooter_string',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Texte de pied de page',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'pagefooter_margin',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-pagefooter_margin',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Marge du pied de page',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'pagefooter_font_family',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'id' => 'documentpdf-pagefooter_font_family',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Police du pied de page',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'empty_option' => 'Choisissez une police',
                'value_options' => $fonts->getFonts(),
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'pagefooter_font_style',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-pagefooter_font_style',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Style police du pied de page',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'pagefooter_font_size',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-pagefooter_font_size',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Taille police du pied de page',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'pagefooter_text_color',
            'type' => 'Zend\Form\Element\Color',
            'attributes' => [
                'id' => 'documentpdf-pagefooter_text_color',
                'class' => 'sbm-width-10c'
            ],
            'options' => [
                'label' => 'Couleur du texte dans le pied de page',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'pagefooter_line_color',
            'type' => 'Zend\Form\Element\Color',
            'attributes' => [
                'id' => 'documentpdf-pagefooter_line_color',
                'class' => 'sbm-width-10c'
            ],
            'options' => [
                'label' => 'Couleur des traits dans le pied de page',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'page_templateId',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'id' => 'documentpdf-page_templateId',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Modèle de page',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'empty_option' => 'Choisissez un modèle',
                'value_options' => $this->getTemplateList('docbody'),
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'page_format',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'id' => 'documentpdf-page_format',
                'class' => 'sbm-width-35c'
            ],
            'options' => [
                'label' => 'Format de la page',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'empty_option' => 'Choisissez un format',
                'value_options' => $this->getArrayPageFormats(),
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'page_orientation',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => [
                'id' => 'documentpdf-page_orientation',
                'class' => 'sbm-radio'
            ],
            'options' => [
                'label' => 'Orientation de la page',
                'label_attributes' => [
                    'class' => 'sbm-label-radio'
                ],
                'value_options' => [
                    'P' => 'Portrait',
                    'L' => 'Paysage'
                ]
            ]
        ]);
        $this->add([
            'name' => 'page_margin_top',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-page_margin_top',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Marge du haut',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'page_margin_bottom',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-page_margin_bottom',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Marge du bas',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'page_margin_left',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-page_margin_left',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Marge de gauche',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'page_margin_right',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-page_margin_right',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Marge de droite',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'main_font_family',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'id' => 'documentpdf-main_font_family',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Police principale',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'empty_option' => 'Choisissez une police',
                'value_options' => $fonts->getFonts(),
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'main_font_style',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-main_font_style',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Style de la police principale',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'main_font_size',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-main_font_size',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Taille de la police principale',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'data_font_family',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'id' => 'documentpdf-data_font_family',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Police des données',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'empty_option' => 'Choisissez une police',
                'value_options' => $fonts->getFonts(),
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'data_font_style',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-data_font_style',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Style de la police des données',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'data_font_size',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-data_font_size',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Taille de la police des données',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'titre1_font_family',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'id' => 'documentpdf-titre1_font_family',
                'class' => 'sbm-width-35c'
            ],
            'options' => [
                'label' => 'Police des Titre1',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'empty_option' => 'Choisissez une police',
                'value_options' => $fonts->getFonts(),
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'titre1_font_style',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-titre1_font_style',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Style de la police des Titre1',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'titre1_font_size',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-titre1_font_size',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Taille de la police des Titre1',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'titre1_text_color',
            'type' => 'Zend\Form\Element\Color',
            'attributes' => [
                'id' => 'documentpdf-titre1_text_color',
                'class' => 'sbm-width-10c'
            ],
            'options' => [
                'label' => 'Couleur du titre 1',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'titre1_line',
            'attributes' => [
                'id' => 'documentpdf-titre1_line',
                'class' => 'sbm-checkbox'
            ],
            'options' => [
                'label' => 'Encadrement de titre 1 ?',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'titre1_line_color',
            'type' => 'Zend\Form\Element\Color',
            'attributes' => [
                'id' => 'documentpdf-titre1_line_color',
                'class' => 'sbm-width-10c'
            ],
            'options' => [
                'label' => 'Couleur encadrement titre 1',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        
        $this->add([
            'name' => 'titre2_font_family',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'id' => 'documentpdf-titre2_font_family',
                'class' => 'sbm-width-35c'
            ],
            'options' => [
                'label' => 'Police des Titre2',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'empty_option' => 'Choisissez une police',
                'value_options' => $fonts->getFonts(),
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'titre2_font_style',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-titre2_font_style',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Style de la police des Titre2',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'titre2_font_size',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-titre2_font_size',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Taille de la police des Titre2',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'titre2_text_color',
            'type' => 'Zend\Form\Element\Color',
            'attributes' => [
                'id' => 'documentpdf-titre2_text_color',
                'class' => 'sbm-width-10c'
            ],
            'options' => [
                'label' => 'Couleur du titre 2',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'titre2_line',
            'attributes' => [
                'id' => 'documentpdf-titre2_line',
                'class' => 'sbm-checkbox'
            ],
            'options' => [
                'label' => 'Encadrement de titre 2 ?',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'titre2_line_color',
            'type' => 'Zend\Form\Element\Color',
            'attributes' => [
                'id' => 'documentpdf-titre2_line_color',
                'class' => 'sbm-width-10c'
            ],
            'options' => [
                'label' => 'Couleur encadrement titre 2',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        
        $this->add([
            'name' => 'titre3_font_family',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'id' => 'documentpdf-titre3_font_family',
                'class' => 'sbm-width-35c'
            ],
            'options' => [
                'label' => 'Police des Titre3',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'empty_option' => 'Choisissez une police',
                'value_options' => $fonts->getFonts(),
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'titre3_font_style',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-titre3_font_style',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Style de la police des Titre3',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'titre3_font_size',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-titre3_font_size',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Taille de la police des Titre3',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'titre3_text_color',
            'type' => 'Zend\Form\Element\Color',
            'attributes' => [
                'id' => 'documentpdf-titre3_text_color',
                'class' => 'sbm-width-10c'
            ],
            'options' => [
                'label' => 'Couleur du titre 3',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'titre3_line',
            'attributes' => [
                'id' => 'documentpdf-titre3_line',
                'class' => 'sbm-checkbox'
            ],
            'options' => [
                'label' => 'Encadrement de titre 3 ?',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'titre3_line_color',
            'type' => 'Zend\Form\Element\Color',
            'attributes' => [
                'id' => 'documentpdf-titre3_line_color',
                'class' => 'sbm-width-10c'
            ],
            'options' => [
                'label' => 'Couleur encadrement titre 3',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        
        $this->add([
            'name' => 'titre4_font_family',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'id' => 'documentpdf-titre4_font_family',
                'class' => 'sbm-width-35c'
            ],
            'options' => [
                'label' => 'Police des Titre4',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'empty_option' => 'Choisissez une police',
                'value_options' => $fonts->getFonts(),
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'titre4_font_style',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-titre4_font_style',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Style de la police des Titre4',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'titre4_font_size',
            'type' => 'text',
            'attributes' => [
                'id' => 'documentpdf-titre4_font_size',
                'class' => 'sbm-width-5c'
            ],
            'options' => [
                'label' => 'Taille de la police des Titre4',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'titre4_text_color',
            'type' => 'Zend\Form\Element\Color',
            'attributes' => [
                'id' => 'documentpdf-titre4_text_color',
                'class' => 'sbm-width-10c'
            ],
            'options' => [
                'label' => 'Couleur du titre 4',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'titre4_line',
            'attributes' => [
                'id' => 'documentpdf-titre4_line',
                'class' => 'sbm-checkbox'
            ],
            'options' => [
                'label' => 'Encadrement de titre 4 ?',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'titre4_line_color',
            'type' => 'Zend\Form\Element\Color',
            'attributes' => [
                'id' => 'documentpdf-titre4_line_color',
                'class' => 'sbm-width-10c'
            ],
            'options' => [
                'label' => 'Couleur encadrement titre 4',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        
        $this->add([
            'name' => 'default_font_monospaced',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'id' => 'documentpdf-page_font_monospaced',
                'class' => 'sbm-width-45c'
            ],
            'options' => [
                'label' => 'Police à espacement fixe',
                'label_attributes' => [
                    'class' => 'sbm-label'
                ],
                'empty_option' => 'Choisissez une police',
                'value_options' => $fonts->getFonts(true),
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        $this->add([
            'name' => 'submit',
            'attributes' => [
                'type' => 'submit',
                'value' => 'Enregistrer',
                'id' => 'documentpdf-submit',
                'autofocus' => 'autofocus',
                'class' => 'button default submit'
            ]
        ]);
        $this->add([
            'name' => 'cancel',
            'attributes' => [
                'type' => 'submit',
                'value' => 'Abandonner',
                'id' => 'documentpdf-cancel',
                'class' => 'button default'
            ]
        ]);
        $this->add([
            'name' => 'tableau',
            'attributes' => [
                'type' => 'submit',
                'value' => 'Mise en forme du tableau',
                'id' => 'documentpdf-tableau',
                'class' => 'button default',
                'style' => 'display: none;'
            ]
        ]);
        $this->add([
            'name' => 'texte',
            'attributes' => [
                'type' => 'submit',
                'value' => 'Mise en forme du texte',
                'id' => 'documentpdf-texte',
                'class' => 'button default',
                'style' => 'display: none;'
            ]
        ]);
        $this->add([
            'name' => 'etiquette',
            'attributes' => [
                'type' => 'submit',
                'value' => 'Mise en forme des étiquettes',
                'id' => 'documentpdf-etiquette',
                'class' => 'button default',
                'style' => 'display: none;'
            ]
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'name' => [
                'name' => 'name',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'recordSource' => [
                'name' => 'recordSource',
                'required' => true,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'SbmPdf\Model\Filter\NomTable',
                        'options' => [
                            'db_manager' => $this->db_manager
                        ]
                    ]
                ],
                'validators' => [
                    [
                        'name' => 'SbmPdf\Model\Validator\RecordSource',
                        'options' => [
                            'db_manager' => $this->db_manager,
                            'auth_userId' => $this->auth_userId
                        ]
                    ]
                ]
            ],
            'TrecordSource' => [
                'name' => 'TrecordSource',
                'required' => false
            ],
            
            'RrecordSource' => [
                'name' => 'RrecordSource',
                'required' => false
            ],
            'filter' => [
                'name' => 'filter',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'orderBy' => [
                'name' => 'orderBy',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'out_name' => [
                'name' => 'out_name',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'title' => [
                'name' => 'title',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'docheader_subtitle' => [
                'name' => 'docheader_subtitle',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'author' => [
                'name' => 'author',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'subject' => [
                'name' => 'subject',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'keywords' => [
                'name' => 'keywords',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'docheader_margin' => [
                'name' => 'docheader_margin',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ]
            ],
            'docfooter_margin' => [
                'name' => 'docfooter_margin',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ]
            ],
            'docfooter_templateId' => [
                'name' => 'docfooter_templateId',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'docfooter_title' => [
                'name' => 'docfooter_title',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'docfooter_string' => [
                'name' => 'docfooter_string',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'pageheader_margin' => [
                'name' => 'pageheader_margin',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ]
            ],
            'pageheader_font_size' => [
                'name' => 'pageheader_font_size',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ]
            ],
            'pageheader_logo_width' => [
                'name' => 'pageheader_logo_width',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ]
            ],
            'pageheader_font_style' => [
                'name' => 'pageheader_font_style',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'pageheader_logo' => [
                'name' => 'pageheader_logo',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'pageheader_title' => [
                'name' => 'pageheader_title',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'pageheader_string' => [
                'name' => 'pageheader_string',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'pagefooter_font_size' => [
                'name' => 'pagefooter_font_size',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ]
            ],
            'pagefooter_margin' => [
                'name' => 'pagefooter_margin',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ]
            ],
            'pagefooter_font_style' => [
                'name' => 'pagefooter_font_style',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'pagefooter_string' => [
                'name' => 'pagefooter_string',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'page_margin_top' => [
                'name' => 'page_margin_top',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ]
            ],
            'page_margin_bottom' => [
                'name' => 'page_margin_bottom',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ]
            ],
            'page_margin_left' => [
                'name' => 'page_margin_left',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ]
            ],
            'page_margin_right' => [
                'name' => 'page_margin_right',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ]
            ],
            'main_font_size' => [
                'name' => 'main_font_size',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ]
            ],
            'data_font_size' => [
                'name' => 'data_font_size',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ]
            ],
            'url_path_images' => [
                'name' => 'url_path_images',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'image_blank' => [
                'name' => 'image_blank',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'main_font_style' => [
                'name' => 'main_font_style',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'data_font_style' => [
                'name' => 'data_font_style',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'titre1_font_style' => [
                'name' => 'titre1_font_style',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'titre2_font_style' => [
                'name' => 'titre2_font_style',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'titre3_font_style' => [
                'name' => 'titre3_font_style',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'titre4_font_style' => [
                'name' => 'titre4_font_style',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ]
                ]
            ],
            'titre1_font_size' => [
                'name' => 'titre1_font_size',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ]
            ],
            'titre2_font_size' => [
                'name' => 'titre2_font_size',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ]
            ],
            'titre3_font_size' => [
                'name' => 'titre3_font_size',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ]
            ],
            'titre4_font_size' => [
                'name' => 'titre4_font_size',
                'required' => false,
                'filters' => [
                    [
                        'name' => 'StripTags'
                    ],
                    [
                        'name' => 'StringTrim'
                    ],
                    [
                        'name' => 'SbmCommun\Filter\Decimal',
                        'options' => [
                            'separateur' => '.',
                            'car2sep' => ','
                        ]
                    ]
                ]
            ]
        ];
    }

    public function setData($data)
    {
        $strategieColor = new Color();
        foreach ($data as $key => &$value) {
            if (substr($key, - 6) == '_color') {
                $value = $strategieColor->hydrate($strategieColor->extract($value));
            }
        }
        if (isset($data['recordSource'])) {
            if (empty($data['TrecordSource'])) {
                $data['TrecordSource'] = $data['recordSource'];
            }
            if (empty($data['RrecordSource'])) {
                $data['RrecordSource'] = $data['recordSource'];
            }
        } else {
            unset($data['TrecordSource'], $data['RrecordSource']);
        }
        parent::setData($data);
    }

    /**
     * Le validateur est sur l'élément recordSource (hidden] alors que les contrôles visibles sont TrecordSource (table] ou RrecordSource (requête].
     * 1/ affecter recordSource de la valeur du contrôle visible
     * 2/ calculer isValid(]
     * 3/ si une erreur est sur recordSource, affecter cette erreur sur le contrôle visible
     * 
     * (non-PHPdoc]
     * @see \Zend\Form\Form::isValid(]
     */
    public function isValid()
    {
        $visibleElementName = $this->data['recordSourceType'] . 'recordSource';
        $this->data['recordSource'] = $this->data[$visibleElementName];
        $ok = parent::isValid();
        if (! $ok) {
            $ehidden = $this->get('recordSource');
            if (!empty($ehidden->getMessages())) {
                $evisible = $this->get($visibleElementName);
                $evisible->setMessages($ehidden->getMessages());
            }
        }
        return $ok;
    }

    public function setMaxLength(array $array)
    {
        foreach ($array as $elementName => $maxLength) {
            try {
                $e = $this->get($elementName);
                $type = $e->getAttribute('type');
                if (! is_null($type) && $type == 'text') {
                    $e->setAttribute('maxlength', $maxLength);
                }
            } catch (Exception $e) {}
        }
    }

    public function setValueOptions($element, array $values_options)
    {
        $e = $this->get($element);
        $e->setValueOptions($values_options);
    }

    /**
     * Modification de la méthode en version 2016-2
     * On ne crée plus d'instance pour appeler get_class_methods() mais
     * on passe le nom de la classe.
     * 
     * @param string $section
     * 
     * @return array
     */
    private function getTemplateList($section)
    {
        return StdLib::getParam($section, $this->template_method_list, []);
        /*$templateSectionMethod = 'template' . $section . 'method';
        $methods = get_class_methods(Tcpdf::class);
        $list = [];
        foreach ($methods as $method) {
            if (strpos(strtolower($method), $templateSectionMethod) === 0) {
                $id = substr($method, strlen($templateSectionMethod));
                $list[$id] = Tcpdf::{$method}('?');
            }
        }
        return $list;*/
    }

    private function getArrayPageFormats()
    {
        return [
            'ISO 216 A Series + 2 SIS 014711 extensions' => [
                'label' => 'ISO 216 A Series + 2 SIS 014711 extensions',
                'options' => [
                    'A0' => 'A0 (841x1189 mm ; 33.11x46.81 in)',
                    'A1' => 'A1 (594x841 mm ; 23.39x33.11 in)',
                    'A2' => 'A2 (420x594 mm ; 16.54x23.39 in)',
                    'A3' => 'A3 (297x420 mm ; 11.69x16.54 in)',
                    'A4' => 'A4 (210x297 mm ; 8.27x11.69 in)',
                    'A5' => 'A5 (148x210 mm ; 5.83x8.27 in)',
                    'A6' => 'A6 (105x148 mm ; 4.13x5.83 in)',
                    'A7' => 'A7 (74x105 mm ; 2.91x4.13 in)',
                    'A8' => 'A8 (52x74 mm ; 2.05x2.91 in)',
                    'A9' => 'A9 (37x52 mm ; 1.46x2.05 in)',
                    'A10' => 'A10 (26x37 mm ; 1.02x1.46 in)',
                    'A11' => 'A11 (18x26 mm ; 0.71x1.02 in)',
                    'A12' => 'A12 (13x18 mm ; 0.51x0.71 in)'
                ]
            ],
            'ISO 216 B Series + 2 SIS 014711 extensions' => [
                'label' => 'ISO 216 B Series + 2 SIS 014711 extensions',
                'options' => [
                    'B0' => 'B0 (1000x1414 mm ; 39.37x55.67 in)',
                    'B1' => 'B1 (707x1000 mm ; 27.83x39.37 in)',
                    'B2' => 'B2 (500x707 mm ; 19.69x27.83 in)',
                    'B3' => 'B3 (353x500 mm ; 13.90x19.69 in)',
                    'B4' => 'B4 (250x353 mm ; 9.84x13.90 in)',
                    'B5' => 'B5 (176x250 mm ; 6.93x9.84 in)',
                    'B6' => 'B6 (125x176 mm ; 4.92x6.93 in)',
                    'B7' => 'B7 (88x125 mm ; 3.46x4.92 in)',
                    'B8' => 'B8 (62x88 mm ; 2.44x3.46 in)',
                    'B9' => 'B9 (44x62 mm ; 1.73x2.44 in)',
                    'B10' => 'B10 (31x44 mm ; 1.22x1.73 in)',
                    'B11' => 'B11 (22x31 mm ; 0.87x1.22 in)',
                    'B12' => 'B12 (15x22 mm ; 0.59x0.87 in)'
                ]
            ],
            'ISO 216 C Series + 2 SIS 014711 extensions + 2 EXTENSION' => [
                'label' => 'ISO 216 C Series + 2 SIS 014711 extensions + 2 EXTENSION',
                'options' => [
                    'C0' => 'C0 (917x1297 mm ; 36.10x51.06 in)',
                    'C1' => 'C1 (648x917 mm ; 25.51x36.10 in)',
                    'C2' => 'C2 (458x648 mm ; 18.03x25.51 in)',
                    'C3' => 'C3 (324x458 mm ; 12.76x18.03 in)',
                    'C4' => 'C4 (229x324 mm ; 9.02x12.76 in)',
                    'C5' => 'C5 (162x229 mm ; 6.38x9.02 in)',
                    'C6' => 'C6 (114x162 mm ; 4.49x6.38 in)',
                    'C7' => 'C7 (81x114 mm ; 3.19x4.49 in)',
                    'C8' => 'C8 (57x81 mm ; 2.24x3.19 in)',
                    'C9' => 'C9 (40x57 mm ; 1.57x2.24 in)',
                    'C10' => 'C10 (28x40 mm ; 1.10x1.57 in)',
                    'C11' => 'C11 (20x28 mm ; 0.79x1.10 in)',
                    'C12' => 'C12 (14x20 mm ; 0.55x0.79 in)',
                    'C76' => 'C76 (81x162 mm ; 3.19x6.38 in)',
                    'DL' => 'DL (110x220 mm ; 4.33x8.66 in)'
                ]
            ],
            'SIS 014711 E Series' => [
                'label' => 'SIS 014711 E Series',
                'options' => [
                    'E0' => 'E0 (879x1241 mm ; 34.61x48.86 in)',
                    'E1' => 'E1 (620x879 mm ; 24.41x34.61 in)',
                    'E2' => 'E2 (440x620 mm ; 17.32x24.41 in)',
                    'E3' => 'E3 (310x440 mm ; 12.20x17.32 in)',
                    'E4' => 'E4 (220x310 mm ; 8.66x12.20 in)',
                    'E5' => 'E5 (155x220 mm ; 6.10x8.66 in)',
                    'E6' => 'E6 (110x155 mm ; 4.33x6.10 in)',
                    'E7' => 'E7 (78x110 mm ; 3.07x4.33 in)',
                    'E8' => 'E8 (55x78 mm ; 2.17x3.07 in)',
                    'E9' => 'E9 (39x55 mm ; 1.54x2.17 in)',
                    'E10' => 'E10 (27x39 mm ; 1.06x1.54 in)',
                    'E11' => 'E11 (19x27 mm ; 0.75x1.06 in)',
                    'E12' => 'E12 (13x19 mm ; 0.51x0.75 in)'
                ]
            ],
            'SIS 014711 G Series' => [
                'label' => 'SIS 014711 G Series',
                'options' => [
                    'G0' => 'G0 (958x1354 mm ; 37.72x53.31 in)',
                    'G1' => 'G1 (677x958 mm ; 26.65x37.72 in)',
                    'G2' => 'G2 (479x677 mm ; 18.86x26.65 in)',
                    'G3' => 'G3 (338x479 mm ; 13.31x18.86 in)',
                    'G4' => 'G4 (239x338 mm ; 9.41x13.31 in)',
                    'G5' => 'G5 (169x239 mm ; 6.65x9.41 in)',
                    'G6' => 'G6 (119x169 mm ; 4.69x6.65 in)',
                    'G7' => 'G7 (84x119 mm ; 3.31x4.69 in)',
                    'G8' => 'G8 (59x84 mm ; 2.32x3.31 in)',
                    'G9' => 'G9 (42x59 mm ; 1.65x2.32 in)',
                    'G10' => 'G10 (29x42 mm ; 1.14x1.65 in)',
                    'G11' => 'G11 (21x29 mm ; 0.83x1.14 in)',
                    'G12' => 'G12 (14x21 mm ; 0.55x0.83 in)'
                ]
            ],
            'ISO Press' => [
                'label' => 'ISO Press',
                'options' => [
                    'RA0' => 'RA0 (860x1220 mm ; 33.86x48.03 in)',
                    'RA1' => 'RA1 (610x860 mm ; 24.02x33.86 in)',
                    'RA2' => 'RA2 (430x610 mm ; 16.93x24.02 in)',
                    'RA3' => 'RA3 (305x430 mm ; 12.01x16.93 in)',
                    'RA4' => 'RA4 (215x305 mm ; 8.46x12.01 in)',
                    'SRA0' => 'SRA0 (900x1280 mm ; 35.43x50.39 in)',
                    'SRA1' => 'SRA1 (640x900 mm ; 25.20x35.43 in)',
                    'SRA2' => 'SRA2 (450x640 mm ; 17.72x25.20 in)',
                    'SRA3' => 'SRA3 (320x450 mm ; 12.60x17.72 in)',
                    'SRA4' => 'SRA4 (225x320 mm ; 8.86x12.60 in)'
                ]
            ],
            'German DIN 476' => [
                'label' => 'German DIN 476',
                'options' => [
                    '4A0' => '4A0 (1682x2378 mm ; 66.22x93.62 in)',
                    '2A0' => '2A0 (1189x1682 mm ; 46.81x66.22 in)'
                ]
            ],
            'Variations on the ISO Standard' => [
                'label' => 'Variations on the ISO Standard',
                'options' => [
                    'A2_EXTRA' => 'A2_EXTRA (445x619 mm ; 17.52x24.37 in)',
                    'A3+' => 'A3+ (329x483 mm ; 12.95x19.02 in)',
                    'A3_EXTRA' => 'A3_EXTRA (322x445 mm ; 12.68x17.52 in)',
                    'A3_SUPER' => 'A3_SUPER (305x508 mm ; 12.01x20.00 in)',
                    'SUPER_A3' => 'SUPER_A3 (305x487 mm ; 12.01x19.17 in)',
                    'A4_EXTRA' => 'A4_EXTRA (235x322 mm ; 9.25x12.68 in)',
                    'A4_SUPER' => 'A4_SUPER (229x322 mm ; 9.02x12.68 in)',
                    'SUPER_A4' => 'SUPER_A4 (227x356 mm ; 8.94x14.02 in)',
                    'A4_LONG' => 'A4_LONG (210x348 mm ; 8.27x13.70 in)',
                    'F4' => 'F4 (210x330 mm ; 8.27x12.99 in)',
                    'SO_B5_EXTRA' => 'SO_B5_EXTRA (202x276 mm ; 7.95x10.87 in)',
                    'A5_EXTRA' => 'A5_EXTRA (173x235 mm ; 6.81x9.25 in)'
                ]
            ],
            'ANSI Series' => [
                'label' => 'ANSI Series',
                'options' => [
                    'ANSI_E' => 'ANSI_E (864x1118 mm ; 34.00x44.00 in)',
                    'ANSI_D' => 'ANSI_D (559x864 mm ; 22.00x34.00 in)',
                    'ANSI_C' => 'ANSI_C (432x559 mm ; 17.00x22.00 in)',
                    'ANSI_B' => 'ANSI_B (279x432 mm ; 11.00x17.00 in)',
                    'ANSI_A' => 'ANSI_A (216x279 mm ; 8.50x11.00 in)'
                ]
            ],
            'Traditional \'Loose\' North American Paper Sizes' => [
                'label' => 'Traditional \'Loose\' North American Paper Sizes',
                'options' => [
                    'LEDGER' => 'LEDGER (432x279 mm ; 17.00x11.00 in)',
                    'USLEDGER' => 'USLEDGER (432x279 mm ; 17.00x11.00 in)',
                    'TABLOID' => 'TABLOID (279x432 mm ; 11.00x17.00 in)',
                    'USTABLOID' => 'USTABLOID (279x432 mm ; 11.00x17.00 in)',
                    'BIBLE' => 'BIBLE (279x432 mm ; 11.00x17.00 in)',
                    'ORGANIZERK' => 'ORGANIZERK (279x432 mm ; 11.00x17.00 in)',
                    'LETTER' => 'LETTER (216x279 mm ; 8.50x11.00 in)',
                    'USLETTER' => 'USLETTER (216x279 mm ; 8.50x11.00 in)',
                    'ORGANIZERM' => 'ORGANIZERM (216x279 mm ; 8.50x11.00 in)',
                    'LEGAL' => 'LEGAL (216x356 mm ; 8.50x14.00 in)',
                    'USLEGAL' => 'USLEGAL (216x356 mm ; 8.50x14.00 in)',
                    'GLETTER' => 'GLETTER (203x267 mm ; 8.00x10.50 in)',
                    'GOVERNMENTLETTER' => 'GOVERNMENTLETTER (203x267 mm ; 8.00x10.50 in)',
                    'JLEGAL' => 'JLEGAL (203x127 mm ; 8.00x5.00 in)',
                    'JUNIORLEGAL' => 'JUNIORLEGAL (203x127 mm ; 8.00x5.00 in)'
                ]
            ],
            'Other North American Paper Sizes' => [
                'label' => 'Other North American Paper Sizes',
                'options' => [
                    'QUADDEMY' => 'QUADDEMY (889x1143 mm ; 35.00x45.00 in)',
                    'SUPER_B' => 'SUPER_B (330x483 mm ; 13.00x19.00 in)',
                    'QUARTO' => 'QUARTO (229x279 mm ; 9.00x11.00 in)',
                    'FOLIO' => 'FOLIO (216x330 mm ; 8.50x13.00 in)',
                    'GOVERNMENTLEGAL' => 'GOVERNMENTLEGAL (216x330 mm ; 8.50x13.00 in)',
                    'EXECUTIVE' => 'EXECUTIVE (184x267 mm ; 7.25x10.50 in)',
                    'MONARCH' => 'MONARCH (184x267 mm ; 7.25x10.50 in)',
                    'MEMO' => 'MEMO (140x216 mm ; 5.50x8.50 in)',
                    'STATEMENT' => 'STATEMENT (140x216 mm ; 5.50x8.50 in)',
                    'ORGANIZERL' => 'ORGANIZERL (140x216 mm ; 5.50x8.50 in)',
                    'FOOLSCAP' => 'FOOLSCAP (210x330 mm ; 8.27x13.00 in)',
                    'COMPACT' => 'COMPACT (108x171 mm ; 4.25x6.75 in)',
                    'ORGANIZERJ' => 'ORGANIZERJ (70x127 mm ; 2.75x5.00 in)'
                ]
            ],
            'Canadian standard CAN 2-9.60M' => [
                'label' => 'Canadian standard CAN 2-9.60M',
                'options' => [
                    'P1' => 'P1 (560x860 mm ; 22.05x33.86 in)',
                    'P2' => 'P2 (430x560 mm ; 16.93x22.05 in)',
                    'P3' => 'P3 (280x430 mm ; 11.02x16.93 in)',
                    'P4' => 'P4 (215x280 mm ; 8.46x11.02 in)',
                    'P5' => 'P5 (140x215 mm ; 5.51x8.46 in)',
                    'P6' => 'P6 (107x140 mm ; 4.21x5.51 in)'
                ]
            ],
            'North American Architectural Sizes' => [
                'label' => 'North American Architectural Sizes',
                'options' => [
                    'ARCH_E' => 'ARCH_E (914x1219 mm ; 36.00x48.00 in)',
                    'ARCH_E1' => 'ARCH_E1 (762x1067 mm ; 30.00x42.00 in)',
                    'ARCH_D' => 'ARCH_D (610x914 mm ; 24.00x36.00 in)',
                    'ARCH_C' => 'ARCH_C (457x610 mm ; 18.00x24.00 in)',
                    'ARCH_B' => 'ARCH_B (305x457 mm ; 12.00x18.00 in)',
                    'ARCH_A' => 'ARCH_A (229x305 mm ; 9.00x12.00 in)',
                    'BROADSHEET' => 'BROADSHEET (457x610 mm ; 18.00x24.00 in)'
                ]
            ],
            'Announcement Envelopes' => [
                'label' => 'Announcement Envelopes',
                'options' => [
                    'ANNENV_A2' => 'ANNENV_A2 (111x146 mm ; 4.37x5.75 in)',
                    'ANNENV_A6' => 'ANNENV_A6 (121x165 mm ; 4.75x6.50 in)',
                    'ANNENV_A7' => 'ANNENV_A7 (133x184 mm ; 5.25x7.25 in)',
                    'ANNENV_A8' => 'ANNENV_A8 (140x206 mm ; 5.50x8.12 in)',
                    'ANNENV_A10' => 'ANNENV_A10 (159x244 mm ; 6.25x9.62 in)',
                    'ANNENV_SLIM' => 'ANNENV_SLIM (98x225 mm ; 3.87x8.87 in)'
                ]
            ],
            'Commercial Envelopes' => [
                'label' => 'Commercial Envelopes',
                'options' => [
                    'COMMENV_N6_1/4' => 'COMMENV_N6_1/4 (89x152 mm ; 3.50x6.00 in)',
                    'COMMENV_N6_3/4' => 'COMMENV_N6_3/4 (92x165 mm ; 3.62x6.50 in)',
                    'COMMENV_N8' => 'COMMENV_N8 (98x191 mm ; 3.87x7.50 in)',
                    'COMMENV_N8' => 'COMMENV_N8 (98x225 mm ; 3.87x8.87 in)',
                    'COMMENV_N10' => 'COMMENV_N10 (105x241 mm ; 4.12x9.50 in)',
                    'COMMENV_N11' => 'COMMENV_N11 (114x263 mm ; 4.50x10.37 in)',
                    'COMMENV_N12' => 'COMMENV_N12 (121x279 mm ; 4.75x11.00 in)',
                    'COMMENV_N14' => 'COMMENV_N14 (127x292 mm ; 5.00x11.50 in)'
                ]
            ],
            'Catalogue Envelopes' => [
                'label' => 'Catalogue Envelopes',
                'options' => [
                    'CATENV_N1' => 'CATENV_N1 (152x229 mm ; 6.00x9.00 in)',
                    'CATENV_N1_3/4' => 'CATENV_N1_3/4 (165x241 mm ; 6.50x9.50 in)',
                    'CATENV_N1_3/4' => 'CATENV_N2 (165x254 mm ; 6.50x10.00 in)',
                    'CATENV_N3' => 'CATENV_N3 (178x254 mm ; 7.00x10.00 in)',
                    'CATENV_N6' => 'CATENV_N6 (191x267 mm ; 7.50x10.50 in)',
                    'CATENV_N7' => 'CATENV_N7 (203x279 mm ; 8.00x11.00 in)',
                    'CATENV_N8' => 'CATENV_N8 (210x286 mm ; 8.25x11.25 in)',
                    'CATENV_N9_1/2' => 'CATENV_N9_1/2 (216x267 mm ; 8.50x10.50 in)',
                    'CATENV_N9_3/4' => 'CATENV_N9_3/4 (222x286 mm ; 8.75x11.25 in)',
                    'CATENV_N10_1/2' => 'CATENV_N10_1/2 (229x305 mm ; 9.00x12.00 in)',
                    'CATENV_N12_1/2' => 'CATENV_N12_1/2 (241x318 mm ; 9.50x12.50 in)',
                    'CATENV_N13_1/2' => 'CATENV_N13_1/2 (254x330 mm ; 10.00x13.00 in)',
                    'CATENV_N14_1/4' => 'CATENV_N14_1/4 (286x311 mm ; 11.25x12.25 in)',
                    'CATENV_N14_1/2' => 'CATENV_N14_1/2 (292x368 mm ; 11.50x14.50 in)'
                ]
            ],
            'Japanese (JIS P 0138-61] Standard B-Series' => [
                'label' => 'Japanese (JIS P 0138-61] Standard B-Series',
                'options' => [
                    'JIS_B0' => 'JIS_B0 (1030x1456 mm ; 40.55x57.32 in)',
                    'JIS_B1' => 'JIS_B1 (728x1030 mm ; 28.66x40.55 in)',
                    'JIS_B2' => 'JIS_B2 (515x728 mm ; 20.28x28.66 in)',
                    'JIS_B3' => 'JIS_B3 (364x515 mm ; 14.33x20.28 in)',
                    'JIS_B4' => 'JIS_B4 (257x364 mm ; 10.12x14.33 in)',
                    'JIS_B5' => 'JIS_B5 (182x257 mm ; 7.17x10.12 in)',
                    'JIS_B6' => 'JIS_B6 (128x182 mm ; 5.04x7.17 in)',
                    'JIS_B7' => 'JIS_B7 (91x128 mm ; 3.58x5.04 in)',
                    'JIS_B8' => 'JIS_B8 (64x91 mm ; 2.52x3.58 in)',
                    'JIS_B9' => 'JIS_B9 (45x64 mm ; 1.77x2.52 in)',
                    'JIS_B10' => 'JIS_B10 (32x45 mm ; 1.26x1.77 in)',
                    'JIS_B11' => 'JIS_B11 (22x32 mm ; 0.87x1.26 in)',
                    'JIS_B12' => 'JIS_B12 (16x22 mm ; 0.63x0.87 in)'
                ]
            ],
            'PA Series' => [
                'label' => 'PA Series',
                'options' => [
                    'PA0' => 'PA0 (840x1120 mm ; 33.07x44.09 in)',
                    'PA1' => 'PA1 (560x840 mm ; 22.05x33.07 in)',
                    'PA2' => 'PA2 (420x560 mm ; 16.54x22.05 in)',
                    'PA3' => 'PA3 (280x420 mm ; 11.02x16.54 in)',
                    'PA4' => 'PA4 (210x280 mm ; 8.27x11.02 in)',
                    'PA5' => 'PA5 (140x210 mm ; 5.51x8.27 in)',
                    'PA6' => 'PA6 (105x140 mm ; 4.13x5.51 in)',
                    'PA7' => 'PA7 (70x105 mm ; 2.76x4.13 in)',
                    'PA8' => 'PA8 (52x70 mm ; 2.05x2.76 in)',
                    'PA9' => 'PA9 (35x52 mm ; 1.38x2.05 in)',
                    'PA10' => 'PA10 (26x35 mm ; 1.02x1.38 in)'
                ]
            ],
            'Standard Photographic Print Sizes' => [
                'label' => 'Standard Photographic Print Sizes',
                'options' => [
                    'PASSPORT_PHOTO' => 'PASSPORT_PHOTO (35x45 mm ; 1.38x1.77 in)',
                    'E' => 'E (82x120 mm ; 3.25x4.72 in)',
                    '3R' => '3R (89x127 mm ; 3.50x5.00 in)',
                    'L' => 'L (89x127 mm ; 3.50x5.00 in)',
                    '4R' => '4R (102x152 mm ; 4.02x5.98 in)',
                    'KG' => 'KG (102x152 mm ; 4.02x5.98 in)',
                    '4D' => '4D (120x152 mm ; 4.72x5.98 in)',
                    '5R' => '5R (127x178 mm ; 5.00x7.01 in)',
                    '2L' => '2L (127x178 mm ; 5.00x7.01 in)',
                    '6R' => '6R (152x203 mm ; 5.98x7.99 in)',
                    '8P' => '8P (152x203 mm ; 5.98x7.99 in)',
                    '8R' => '8R (203x254 mm ; 7.99x10.00 in)',
                    '6P' => '6P (203x254 mm ; 7.99x10.00 in)',
                    'S8R' => 'S8R (203x305 mm ; 7.99x12.01 in)',
                    '6PW' => '6PW (203x305 mm ; 7.99x12.01 in)',
                    '10R' => '10R (254x305 mm ; 10.00x12.01 in)',
                    '4P' => '4P (254x305 mm ; 10.00x12.01 in)',
                    'S10R' => 'S10R (254x381 mm ; 10.00x15.00 in)',
                    '4PW' => '4PW (254x381 mm ; 10.00x15.00 in)',
                    '11R' => '11R (279x356 mm ; 10.98x14.02 in)',
                    'S11R' => 'S11R (279x432 mm ; 10.98x17.01 in)',
                    '12R' => '12R (305x381 mm ; 12.01x15.00 in)',
                    '12R' => 'S12R (305x456 mm ; 12.01x17.95 in)'
                ]
            ],
            'Common Newspaper Sizes' => [
                'label' => 'Common Newspaper Sizes',
                'options' => [
                    'NEWSPAPER_BROADSHEET' => 'NEWSPAPER_BROADSHEET (750x600 mm ; 29.53x23.62 in)',
                    'NEWSPAPER_BERLINER' => 'NEWSPAPER_BERLINER (470x315 mm ; 18.50x12.40 in)',
                    'NEWSPAPER_COMPACT' => 'NEWSPAPER_COMPACT (430x280 mm ; 16.93x11.02 in)',
                    'NEWSPAPER_TABLOID' => 'NEWSPAPER_TABLOID (430x280 mm ; 16.93x11.02 in)'
                ]
            ],
            'Business Cards' => [
                'label' => 'Business Cards',
                'options' => [
                    'CREDIT_CARD' => 'CREDIT_CARD (54x86 mm ; 2.13x3.37 in)',
                    'BUSINESS_CARD' => 'BUSINESS_CARD (54x86 mm ; 2.13x3.37 in)',
                    'BUSINESS_CARD_ISO7810' => 'BUSINESS_CARD_ISO7810 (54x86 mm ; 2.13x3.37 in)',
                    'BUSINESS_CARD_ISO216' => 'BUSINESS_CARD_ISO216 (52x74 mm ; 2.05x2.91 in)',
                    'BUSINESS_CARD_IT' => 'BUSINESS_CARD_IT (55x85 mm ; 2.17x3.35 in)',
                    'BUSINESS_CARD_UK' => 'BUSINESS_CARD_UK (55x85 mm ; 2.17x3.35 in)',
                    'BUSINESS_CARD_FR' => 'BUSINESS_CARD_FR (55x85 mm ; 2.17x3.35 in)',
                    'BUSINESS_CARD_DE' => 'BUSINESS_CARD_DE (55x85 mm ; 2.17x3.35 in)',
                    'BUSINESS_CARD_ES' => 'BUSINESS_CARD_ES (55x85 mm ; 2.17x3.35 in)',
                    'BUSINESS_CARD_US' => 'BUSINESS_CARD_US (51x89 mm ; 2.01x3.50 in)',
                    'BUSINESS_CARD_CA' => 'BUSINESS_CARD_CA (51x89 mm ; 2.01x3.50 in)',
                    'BUSINESS_CARD_JP' => 'BUSINESS_CARD_JP (55x91 mm ; 2.17x3.58 in)',
                    'BUSINESS_CARD_HK' => 'BUSINESS_CARD_HK (54x90 mm ; 2.13x3.54 in)',
                    'BUSINESS_CARD_AU' => 'BUSINESS_CARD_AU (55x90 mm ; 2.17x3.54 in)',
                    'BUSINESS_CARD_DK' => 'BUSINESS_CARD_DK (55x90 mm ; 2.17x3.54 in)',
                    'BUSINESS_CARD_SE' => 'BUSINESS_CARD_SE (55x90 mm ; 2.17x3.54 in)',
                    'BUSINESS_CARD_RU' => 'BUSINESS_CARD_RU (50x90 mm ; 1.97x3.54 in)',
                    'BUSINESS_CARD_CZ' => 'BUSINESS_CARD_CZ (50x90 mm ; 1.97x3.54 in)',
                    'BUSINESS_CARD_FI' => 'BUSINESS_CARD_FI (50x90 mm ; 1.97x3.54 in)',
                    'BUSINESS_CARD_HU' => 'BUSINESS_CARD_HU (50x90 mm ; 1.97x3.54 in)',
                    'BUSINESS_CARD_IL' => 'BUSINESS_CARD_IL (50x90 mm ; 1.97x3.54 in)'
                ]
            ],
            'Billboards' => [
                'label' => 'Billboards',
                'options' => [
                    '4SHEET' => '4SHEET (1016x1524 mm ; 40.00x60.00 in)',
                    '6SHEET' => '6SHEET (1200x1800 mm ; 47.24x70.87 in)',
                    '12SHEET' => '12SHEET (3048x1524 mm ; 120.00x60.00 in)',
                    '16SHEET' => '16SHEET (2032x3048 mm ; 80.00x120.00 in)',
                    '32SHEET' => '32SHEET (4064x3048 mm ; 160.00x120.00 in)',
                    '48SHEET' => '48SHEET (6096x3048 mm ; 240.00x120.00 in)',
                    '64SHEET' => '64SHEET (8128x3048 mm ; 320.00x120.00 in)',
                    '96SHEET' => '96SHEET (12192x3048 mm ; 480.00x120.00 in)'
                ]
            ],
            'Old Imperial English (some are still used in USA]' => [
                'label' => 'Old Imperial English (some are still used in USA]',
                'options' => [
                    'EN_EMPEROR' => 'EN_EMPEROR (1219x1829 mm ; 48.00x72.00 in)',
                    'EN_ANTIQUARIAN' => 'EN_ANTIQUARIAN (787x1346 mm ; 31.00x53.00 in)',
                    'EN_GRAND_EAGLE' => 'EN_GRAND_EAGLE (730x1067 mm ; 28.75x42.00 in)',
                    'EN_DOUBLE_ELEPHANT' => 'EN_DOUBLE_ELEPHANT (679x1016 mm ; 26.75x40.00 in)',
                    'EN_ATLAS' => 'EN_ATLAS (660x864 mm ; 26.00x34.00 in)',
                    'EN_COLOMBIER' => 'EN_COLOMBIER (597x876 mm ; 23.50x34.50 in)',
                    'EN_ELEPHANT' => 'EN_ELEPHANT (584x711 mm ; 23.00x28.00 in)',
                    'EN_DOUBLE_DEMY' => 'EN_DOUBLE_DEMY (572x902 mm ; 22.50x35.50 in)',
                    'EN_IMPERIAL' => 'EN_IMPERIAL (559x762 mm ; 22.00x30.00 in)',
                    'EN_PRINCESS' => 'EN_PRINCESS (546x711 mm ; 21.50x28.00 in)',
                    'EN_CARTRIDGE' => 'EN_CARTRIDGE (533x660 mm ; 21.00x26.00 in)',
                    'EN_DOUBLE_LARGE_POST' => 'EN_DOUBLE_LARGE_POST (533x838 mm ; 21.00x33.00 in)',
                    'EN_ROYAL' => 'EN_ROYAL (508x635 mm ; 20.00x25.00 in)',
                    'EN_SHEET' => 'EN_SHEET (495x597 mm ; 19.50x23.50 in)',
                    'EN_HALF_POST' => 'EN_HALF_POST (495x597 mm ; 19.50x23.50 in)',
                    'EN_SUPER_ROYAL' => 'EN_SUPER_ROYAL (483x686 mm ; 19.00x27.00 in)',
                    'EN_DOUBLE_POST' => 'EN_DOUBLE_POST (483x775 mm ; 19.00x30.50 in)',
                    'EN_MEDIUM' => 'EN_MEDIUM (445x584 mm ; 17.50x23.00 in)',
                    'EN_DEMY' => 'EN_DEMY (445x572 mm ; 17.50x22.50 in)',
                    'EN_LARGE_POST' => 'EN_LARGE_POST (419x533 mm ; 16.50x21.00 in)',
                    'EN_COPY_DRAUGHT' => 'EN_COPY_DRAUGHT (406x508 mm ; 16.00x20.00 in)',
                    'EN_POST' => 'EN_POST (394x489 mm ; 15.50x19.25 in)',
                    'EN_CROWN' => 'EN_CROWN (381x508 mm ; 15.00x20.00 in)',
                    'EN_PINCHED_POST' => 'EN_PINCHED_POST (375x470 mm ; 14.75x18.50 in)',
                    'EN_BRIEF' => 'EN_BRIEF (343x406 mm ; 13.50x16.00 in)',
                    'EN_FOOLSCAP' => 'EN_FOOLSCAP (343x432 mm ; 13.50x17.00 in)',
                    'EN_SMALL_FOOLSCAP' => 'EN_SMALL_FOOLSCAP (337x419 mm ; 13.25x16.50 in)',
                    'EN_POTT' => 'EN_POTT (318x381 mm ; 12.50x15.00 in)'
                ]
            ],
            'Old Imperial Belgian' => [
                'label' => 'Old Imperial Belgian',
                'options' => [
                    'BE_GRAND_AIGLE' => 'BE_GRAND_AIGLE (700x1040 mm ; 27.56x40.94 in)',
                    'BE_COLOMBIER' => 'BE_COLOMBIER (620x850 mm ; 24.41x33.46 in)',
                    'BE_DOUBLE_CARRE' => 'BE_DOUBLE_CARRE (620x920 mm ; 24.41x36.22 in)',
                    'BE_ELEPHANT' => 'BE_ELEPHANT (616x770 mm ; 24.25x30.31 in)',
                    'BE_PETIT_AIGLE' => 'BE_PETIT_AIGLE (600x840 mm ; 23.62x33.07 in)',
                    'BE_GRAND_JESUS' => 'BE_GRAND_JESUS (550x730 mm ; 21.65x28.74 in)',
                    'BE_JESUS' => 'BE_JESUS (540x730 mm ; 21.26x28.74 in)',
                    'BE_RAISIN' => 'BE_RAISIN (500x650 mm ; 19.69x25.59 in)',
                    'BE_GRAND_MEDIAN' => 'BE_GRAND_MEDIAN (460x605 mm ; 18.11x23.82 in)',
                    'BE_DOUBLE_POSTE' => 'BE_DOUBLE_POSTE (435x565 mm ; 17.13x22.24 in)',
                    'BE_COQUILLE' => 'BE_COQUILLE (430x560 mm ; 16.93x22.05 in)',
                    'BE_PETIT_MEDIAN' => 'BE_PETIT_MEDIAN (415x530 mm ; 16.34x20.87 in)',
                    'BE_RUCHE' => 'BE_RUCHE (360x460 mm ; 14.17x18.11 in)',
                    'BE_PROPATRIA' => 'BE_PROPATRIA (345x430 mm ; 13.58x16.93 in)',
                    'BE_LYS' => 'BE_LYS (317x397 mm ; 12.48x15.63 in)',
                    'BE_POT' => 'BE_POT (307x384 mm ; 12.09x15.12 in)',
                    'BE_ROSETTE' => 'BE_ROSETTE (270x347 mm ; 10.63x13.66 in)'
                ]
            ],
            'Old Imperial French' => [
                'label' => 'Old Imperial French',
                'options' => [
                    'FR_UNIVERS' => 'FR_UNIVERS (1000x1300 mm ; 39.37x51.18 in)',
                    'FR_DOUBLE_COLOMBIER' => 'FR_DOUBLE_COLOMBIER (900x1260 mm ; 35.43x49.61 in)',
                    'FR_GRANDE_MONDE' => 'FR_GRANDE_MONDE (900x1260 mm ; 35.43x49.61 in)',
                    'FR_DOUBLE_SOLEIL' => 'FR_DOUBLE_SOLEIL (800x1200 mm ; 31.50x47.24 in)',
                    'FR_DOUBLE_JESUS' => 'FR_DOUBLE_JESUS (760x1120 mm ; 29.92x44.09 in)',
                    'FR_GRAND_AIGLE' => 'FR_GRAND_AIGLE (750x1060 mm ; 29.53x41.73 in)',
                    'FR_PETIT_AIGLE' => 'FR_PETIT_AIGLE (700x940 mm ; 27.56x37.01 in)',
                    'FR_DOUBLE_RAISIN' => 'FR_DOUBLE_RAISIN (650x1000 mm ; 25.59x39.37 in)',
                    'FR_JOURNAL' => 'FR_JOURNAL (650x940 mm ; 25.59x37.01 in)',
                    'FR_COLOMBIER_AFFICHE' => 'FR_COLOMBIER_AFFICHE (630x900 mm ; 24.80x35.43 in)',
                    'FR_DOUBLE_CAVALIER' => 'FR_DOUBLE_CAVALIER (620x920 mm ; 24.41x36.22 in)',
                    'FR_CLOCHE' => 'FR_CLOCHE (600x800 mm ; 23.62x31.50 in)',
                    'FR_SOLEIL' => 'FR_SOLEIL (600x800 mm ; 23.62x31.50 in)',
                    'FR_DOUBLE_CARRE' => 'FR_DOUBLE_CARRE (560x900 mm ; 22.05x35.43 in)',
                    'FR_DOUBLE_COQUILLE' => 'FR_DOUBLE_COQUILLE (560x880 mm ; 22.05x34.65 in)',
                    'FR_JESUS' => 'FR_JESUS (560x760 mm ; 22.05x29.92 in)',
                    'FR_RAISIN' => 'FR_RAISIN (500x650 mm ; 19.69x25.59 in)',
                    'FR_CAVALIER' => 'FR_CAVALIER (460x620 mm ; 18.11x24.41 in)',
                    'FR_DOUBLE_COURONNE' => 'FR_DOUBLE_COURONNE (460x720 mm ; 18.11x28.35 in)',
                    'FR_CARRE' => 'FR_CARRE (450x560 mm ; 17.72x22.05 in)',
                    'FR_COQUILLE' => 'FR_COQUILLE (440x560 mm ; 17.32x22.05 in)',
                    'FR_DOUBLE_TELLIERE' => 'FR_DOUBLE_TELLIERE (440x680 mm ; 17.32x26.77 in)',
                    'FR_DOUBLE_CLOCHE' => 'FR_DOUBLE_CLOCHE (400x600 mm ; 15.75x23.62 in)',
                    'FR_DOUBLE_POT' => 'FR_DOUBLE_POT (400x620 mm ; 15.75x24.41 in)',
                    'FR_ECU' => 'FR_ECU (400x520 mm ; 15.75x20.47 in)',
                    'FR_COURONNE' => 'FR_COURONNE (360x460 mm ; 14.17x18.11 in)',
                    'FR_TELLIERE' => 'FR_TELLIERE (340x440 mm ; 13.39x17.32 in)',
                    'FR_POT' => 'FR_POT (310x400 mm ; 12.20x15.75 in)'
                ]
            ]
        ];
    }
}