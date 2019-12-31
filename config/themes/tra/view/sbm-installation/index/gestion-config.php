<?php
/**
 * Description des pages d'accueil modifiables dans le menu de la page sbm-installation/index/gestion-config.phtml
 *
 * @project sbm
 * @package config/themes/arlysere/view/sbm-installation/index
 * @filesource gestion-config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 déc. 2019
 * @version 2019-2.5.4
 */
echo $this->ligneMenuAction('fam-bullet-go',
    [
        'filename' => 'sbm-front/index/index-avant.inc.phtml'
    ],
    [
        'value' => "Page d'accueil affichée avant l'ouverture des inscriptions",
        'formaction' => $url_edit_page
    ]);

echo $this->ligneMenuAction('fam-bullet-go',
    [
        'filename' => 'sbm-front/index/index-pendant.inc.phtml'
    ],
    [
        'value' => "Page d'accueil affichée pendant la campagne d'inscriptions",
        'formaction' => $url_edit_page
    ]);
echo $this->ligneMenuAction('fam-bullet-go',
    [
        'filename' => 'sbm-front/index/index-apres.inc.phtml'
    ],
    [
        'value' => "Page d'accueil affichée après la fin de la campagne d'inscriptions et avant la fermeture",
        'formaction' => $url_edit_page
    ]);
echo $this->ligneMenuAction('fam-bullet-go',
    [
        'filename' => 'sbm-front/index/index-ferme.inc.phtml'
    ],
    [
        'value' => "Page d'accueil affichée lorsque les inscriptions sont closes",
        'formaction' => $url_edit_page
    ]);
echo $this->ligneMenuAction('fam-bullet-go',
    [
        'filename' => 'sbm-parent/index/index-avant.inc.phtml'
    ],
    [
        'value' => "Page de l'espace parent affichée avant l'ouverture des inscriptions",
        'formaction' => $url_edit_page
    ]);

echo $this->ligneMenuAction('fam-bullet-go',
    [
        'filename' => 'sbm-parent/index/index-pendant.inc.phtml'
    ],
    [
        'value' => "Page de l'espace parent affichée pendant la campagne d'inscriptions",
        'formaction' => $url_edit_page
    ]);
echo $this->ligneMenuAction('fam-bullet-go',
    [
        'filename' => 'sbm-parent/index/index-apres.inc.phtml'
    ],
    [
        'value' => "Page de l'espace parent affichée après la fin de la campagne d'inscriptions et avant la fermeture",
        'formaction' => $url_edit_page
    ]);
echo $this->ligneMenuAction('fam-bullet-go',
    [
        'filename' => 'sbm-parent/index/index-ferme.inc.phtml'
    ],
    [
        'value' => "Page de l'espace parent affichée lorsque les inscriptions sont closes",
        'formaction' => $url_edit_page
    ]);
echo $this->ligneMenuAction('fam-bullet-go',
    [
        'filename' => 'sbm-front/login/contact.inc.phtml'
    ],
    [
        'value' => "Page de contact pour les utilisateurs non identifiés",
        'formaction' => $url_edit_page
    ]);
echo $this->ligneMenuAction('fam-bullet-go',
    [
        'filename' => 'sbm-mail/index/index.inc.phtml'
    ],
    [
        'value' => "Information de contact affichée dans le formulaire d'envoi d'un message par mail",
        'formaction' => $url_edit_page
    ]);
