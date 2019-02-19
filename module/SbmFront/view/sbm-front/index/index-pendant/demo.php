<?php
/**
 * Adaptation de la page d'accueil pour la version Démo
 *
 * Page d'accueil abant ouverture de la campagne de saisie
 * 
 * @project sbm
 * @package module/SbmFront/view/sbm-front/index/index-pendant
 * @filesource decazeville.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 sept. 2018
 * @version 2019-2.5.0
 */
$adresse = implode('<br>', $this->client['adresse']);

$format = <<<EOT
<p>La période d'essai %s du site de démonstration est ouvert du %s au %s.</p>
<p>Durant cette période, vous pouvez créer votre propre compte en tant que parent et simuler une ou plusieurs inscriptions.</p>
<p>Vous pouvez également entrer avec les rôles suivants :</p><ul>
<li>en tant que <i>parent</i> avec le compte <i>parent.demo@dafap.fr</i> ;</li>
<li>en tant que <i>administrateur de l'application</i> avec le compte <i>admin.demo@dafap.fr</i> ;</li>
<li>en tant que <i>gestionnaire du service de transport</i> avec le compte <i>gestion.demo@dafap.fr</i> ;</li>
<li>en tant que <i>secrétariat de votre organisation</i> avec le compte <i>secretariat.demo@dafap.fr</i> ;</li>
<li>en tant que <i>établissement</i> avec le compte <i>etablissement.demo@dafap.fr</i> ;</li>
<li>en tant que <i>transporteur</i> avec le compte <i>transporteur.demo@dafap.fr</i>.</li></ul>
<p>Pour tous les comptes démo, le mot de passe est <i>essai33SBM</i> (le même pour tous).</p>
<p>Pour tout autre renseignement, vous pouvez vous adresser à :</p>
<div class="centre">
<a href="%s" class="accueil">%s</a><br>%s<br>%s %s<br>Tél. %s<br>%s
</div>
EOT;

return sprintf($format, $this->as, $this->etat['dateDebut']->format('d/m/Y'),
    $this->etat['dateFin']->format('d/m/Y'), $this->accueil, $this->client['name'],
    $adresse, $this->client['code_postal'], $this->client['commune'],
    $this->telephone($this->client['telephone']), $this->client['email']);