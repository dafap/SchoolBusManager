<?php
/**
 * Adaptation de la page d'accueil pour Decazeville
 *
 * Page d'accueil après la période d'inscription :
 * - message durant la période allant de la fin de la période d'inscription au début de l'année scolaire
 * - message après le début de l'année scolaire
 *
 * Utilise $dateDebutAs définie dans index-apres.phtml
 *
 * @project sbm
 * @package module/SbmFront/view/sbm-front/index/index-avant
 * @filesource decazeville.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 sept. 2018
 * @version 2019-2.5.0
 */
if ($this->dateDebutAs >= date('Y-m-d')) {
    /**
     * entre la fin de la période d'inscription et le début de l'année scolaire
     */
    $liste_permanences = implode("</li>\n<li>", $this->permanences);
    $format = <<<EOT
<p class="premiere">La période d'inscription %s est close.</p>
<p>Si vous avez inscrit vos enfants et payé en ligne vous recevrez les cartes de transport courant août.
Sinon, pensez à venir à la Communauté de communes lors des permanences (9h-12h30 et 13h30-17h30)
    organisées par date pour les habitants de :
<ul>
<li>%s</li>
</ul>La carte de transport sera donnée sur place.</p>
<p>Si vous avez indiqué votre adresse email, vous pouvez consulter l’affectation de vos enfants,
    trouver les horaires, correspondre avec le service grâce à votre compte personnel.</p>
<p>Pour tout renseignement ou pour une nouvelle inscription, adressez vous directement au
    service Transport à l'adresse :</p>
<div class="centre">
<a href="%s" class="accueil">%s</a><br>
%s<br>
%s<br>
%s %s<br>
Tél : %s<br>
Mail : %s</br>

</div>
EOT;

    $msg = sprintf($format, $this->as, $liste_permanences, $this->accueil,
        $this->client['name'], $this->client['adresse'][0], $this->client['adresse'][1],
        $this->client['code_postal'], $this->client['commune'],
        $this->telephone($this->client['telephone']), $this->client['email']);
} else {
    /**
     * après le début de l'année scolaire
     */
    $format = <<<EOT
<p class="premiere">La période d'inscription %s est close.</p>
<p>Si vous avez indiqué votre adresse email, vous pouvez consulter l’affectation de vos enfants,
    trouver les horaires, correspondre avec le service grâce à votre compte personnel.</p>
<p>Pour tout renseignement ou pour une nouvelle inscription, adressez vous directement au
    service Transport à l'adresse :</p>
<div class="centre">
<a href="%s" class="accueil">%s</a><br>
%s<br>
%s<br>
%s %s<br>
Tél : %s<br>
Mail : %s</br>

</div>
EOT;

    $msg = sprintf($format, $this->as, $this->accueil, $this->client['name'],
        $this->client['adresse'][0], $this->client['adresse'][1],
        $this->client['code_postal'], $this->client['commune'],
        $this->telephone($this->client['telephone']), $this->client['email']);
}

return $msg;