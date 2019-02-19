<?php
/**
 * Adaptation de la page d'accueil pour Decazeville
 *
 * Page d'accueil avant ouverture de la campagne de saisie
 * 
 * @project sbm
 * @package module/SbmFront/view/sbm-front/index/index-avant
 * @filesource decazeville.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 sept. 2018
 * @version 2019-2.5.0
 */
$communes_membres = implode(', ', $this->communes_membres);
$liste_permanences = implode("</li>\n<li>", $this->permanences);
$nb_communes_membres = count($this->communes_membres);

$format = <<<EOT
<p class="premiere">La période d'inscription %s sera ouverte du %s au %s.
    <div id="help-delai" class="orange cursor-help"><i>Cas particulier pour les élèves en
    attente d'orientation scolaire</i> <i class="fam-help"></i></div></p>
<p id="help-delai-content" class="info cursor-zoom-out" style="display: none;">
    <i class="fam-cancel"></i> Un délai supplémentaire sera accordé jusqu’au %s pour
    les élèves en attente d’orientation scolaire. Passé ce délai, les inscriptions retardataires
    ne pourront être traitées qu’à la rentrée et dans la limite des places disponibles dans les
    véhicules.</p>
<p>Durant cette période, depuis ce site vous pourrez inscrire vos enfants s'ils sont à la fois
    domiciliés et scolarisés dans l’une des %d communes de %s: %s.</p>
<div id="inscription">
<span id="help-autre" class="orange cursor-help"><i>Pour les autres cas, cliquez ici pour obtenir
    des informations</i> <i class="fam-help"></i></span>
<p>Si votre enfant était déjà inscrit au transport scolaire l’an dernier, vos coordonnées seront
conservées sur ce site, vous n’aurez plus qu’à modifier la classe et l’établissement si besoin.</p>
<p>Vous accèderez à votre dossier à l’aide de votre adresse mail qui est votre identifiant.</p>
<p>Les inscriptions ne seront définitives qu’après paiement de la carte de transport.
Les paiements pouront se faire:</p>
<ul>
<li>par carte bancaire sur ce site pour recevoir la carte de transport scolaire par courrier courant août ;</li>
<li>par chèque ou espèces à la Communauté de communes lors des permanences (9h-12h30 et 13h30-17h30)
    organisées par date pour les habitants de :
<ul>
<li>%s</li>
</ul>La carte de transport sera donnée sur place.
</li>
</ul>
</div>
<div id="help-autre-content" style="display: none;">
<p>Cas particulier des élèves limitrophes domiciliés à Rulhe-Auzits, Grand-Vabre et dans le
    Cantal et scolarisés dans l’une des %d communes : l’inscription sur ce site ne sera pas disponible,
    vous devrez contacter le service transport qui prendra votre inscription :</p>
<div class="centre">
Tél : %s<br>
Mail : %s</br>
%s – %s – %s – %s %s
</div>
<p>Si votre enfant est scolarisé en dehors des %d communes, vous devrez vous inscrire auprès
    du service Régional des transports de l’Aveyron à l’adresse : <a href="%s">%s</a></p>
<span id="help-retour" class="orange cursor-zoom-out"><i class="fam-cancel"></i>
    <i>Retour à la page précédente</i></span>
</div>
EOT;

return sprintf($format,
    $this->as,
    $this->etat['dateDebut']->format('d/m/Y'),
    $this->etat['dateFin']->format('d/m/Y'),
    $this->etat['echeance']->format('d/m/Y'),
    $nb_communes_membres,
    $this->client['name'],
    $communes_membres,
    $liste_permanences,
    $nb_communes_membres,
    $this->telephone($this->client['telephone']),
    $this->client['email'],
    $this->client['name'],
    $this->client['adresse'][0],
    $this->client['adresse'][1],
    $this->client['code_postal'],
    $this->client['commune'],
    $nb_communes_membres,
    $this->url_ts_region,
    $this->url_ts_region
    );