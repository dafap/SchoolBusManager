#Implémentation du plugin de Paybox

##Documentation

Source : ManuelIntegrationVerifone_PayboxSystem_V8.0_FR.pdf

###Appel de la page de paiement

_Voir page 16_ :
Pour afficher la page de paiement, il suffit d'envoyer à l'URL de Paybox System une requête HTTPS avec certaines variables.

####Préparation du message

Les variables suivantes sont obligatoires dans toute requête :

* PBX_SITE
* PBX_RANG
* PBX_IDENTIFIANT
* PBX_TOTAL		(Montant de la transaction en centimes, sans virgule ni point)
* PBX_DEVISE	(Euro : 978)
* PBX_CMD		(Référence commande côté commerçant : 1 à 250 caractères : unique à chaque appel)
* PBX_PORTEUR	(Adresse E-mail de l’acheteur : 6 à 120 caractères : @ et . doivent être présents)
* PBX_RETOUR	(Liste des variables qui seront retournées par Verifone : _voir ci-dessous_ )
* PBX_HASH		(Type d’algorithme de hachage pour le calcul de l’empreinte. Choix possibles ci-dessous)
* PBX_TIME		(Horodatage de la transaction cad date et heure de la signature. Doit être URL-encodée. La date est au format ISO-8601. En PHP : $dateTime = date("c");)
* PBX_HMAC		(Signature calculée avec la clé secrète. Utiliser l'algorithme indiqué dans PBX_HASH)

En page 17, exemple de paiement en préproduction.

**PBX_RETOUR** ( _pages 47-48_ )

<table>
<tr><td> CODE </td><td> DESCRIPTION </td></tr>
<tr><td> M </td><td> Montant de la transaction (précisé dans PBX_TOTAL). </td></tr>
<tr><td> R </td><td> Référence commande (précisée dans PBX_CMD) : espace URL encodé </td></tr>
<tr><td> T </td><td> Numéro d’appel Paybox </td></tr>
<tr><td> A </td><td> numéro d’Autorisation (numéro remis par le centre d’autorisation) : URL encodé </td></tr>
<tr><td> B </td><td> numéro d’aBonnement (numéro remis par Paybox) </td></tr>
<tr><td> C </td><td> Type de Carte retenu (cf. PBX_TYPECARTE) </td></tr>
<tr><td> D </td><td> Date de fin de validité de la carte du porteur. Format : AAMM </td></tr>
<tr><td> E </td><td> Code réponse de la transaction (cf. Tableau 3 suivant : Codes réponse PBX_RETOUR) </td></tr>
<tr><td> F </td><td> Etat de l’authentiFication du porteur vis-à-vis du programme 3-D Secure :

* Y:Porteur authentifié
* A:Authentification du porteur forcée par la banque de l’acheteur
* U:L’authentification du porteur n’a pas pu s’effectuer
* N:Porteur non authentifié

</td></tr>
<tr><td> G </td><td> Garantie du paiement par le programme 3-D Secure. Format : O ou N </td></tr>
<tr><td> H </td><td> Empreinte de la carte </td></tr>
<tr><td> I </td><td> Code pays de l’adresse IP de l’internaute. Format : ISO 3166 (alphabétique) </td></tr>
<tr><td> J </td><td> 2 derniers chiffres du numéro de carte du porteur </td></tr>
<tr><td> K </td><td> Signature sur les variables de l’URL. Format : url-encodé </td></tr>
<tr><td> N </td><td> 6 premiers chiffres (« biN6 ») du numéro de carte de l’acheteur </td></tr>
<tr><td> O </td><td> EnrOlement du porteur au programme 3-D Secure : </td></tr>

* Y:Porteur enrôlé
* N:Porteur non enrôlé
* U:Information non connue
<tr><td> o </td><td> Spécifique Cetelem : Option de paiement sélectionnée par le client :

* 005 : Comptant
* 001 : Crédit

</td></tr>
<tr><td> P </td><td> Type de Paiement retenu (cf. PBX_TYPEPAIEMENT) </td></tr>
<tr><td> Q </td><td> Heure de traitement de la transaction. Format : HH:MM:SS (24h) </td></tr>
<tr><td> S </td><td> Numéro de TranSaction Paybox </td></tr>
<tr><td> U </td><td> Gestion des abonnements avec le traitement Paybox Direct Plus. </td></tr>
_Pour les paiements par carte :_ 

Handle_Numéro_De_Carte_Crypté++Date_De_Validité_De_La_Carte(format AAMM)++---

Ce champ est URL-encodé. Vous devez conserver la valeur.

_Pour les paiements avec Paypal :_ 

Ce champ contient l’identifiant de l’autorisation fourni par Paypal. Il ne vous sera pas nécessaire pour les paiements suivants.
</td></tr>
<tr><td> V </td><td> Nouvel identifiant de l’abonné sigmaplus. </td></tr>
<tr><td> W </td><td> Date de traitement de la transaction sur la plateforme Paybox. Format : JJMMAAAA </td></tr>
<tr><td> Y </td><td> Code paYs de la banque émettrice de la carte. Format : ISO 3166 (alphabétique) </td></tr>
<tr><td> Z </td><td> Index lors de l’utilisation des paiements mixtes (cartes cadeaux associées à un complément par carte CB/Visa/MasterCard/Amex) </td></tr>
</table>

**Code de réponse PBX_RETOUR** ( _page 48-49_ )

<table>
<tr><td> CODE </td><td> DESCRIPTION </td></tr>
<tr><td> 00000 </td><td> Opération réussie.
<tr><td> 00001 </td><td> La connexion au centre d’autorisation a échoué ou une erreur interne est survenue. Dans ce cas, il est souhaitable de faire une tentative sur le site secondaire : tpeweb1.paybox.com. </td></tr>
<tr><td> 001xx </td><td> Paiement refusé par le centre d’autorisation [voir §12.1 page Codes réponses du centre d’autorisation].
En cas d’autorisation de la transaction par le centre d’autorisation de la banque ou de l’établissement financier privatif, le code erreur “00100” sera en fait remplacé directement par “00000”. </td></tr>
<tr><td> 00003 </td><td> Erreur Paybox. Dans ce cas, il est souhaitable de faire une tentative sur le site secondaire FQDN tpeweb1.paybox.com.
<tr><td> 00004 </td><td> Numéro de porteur ou cryptogramme visuel invalide. </td></tr>
<tr><td> 00006 </td><td> Accès refusé ou site/rang/identifiant incorrect. </td></tr>
<tr><td> 00008 </td><td> Date de fin de validité incorrecte. </td></tr>
<tr><td> 00009 </td><td> Erreur de création d’un abonnement. </td></tr>
<tr><td> 00010 </td><td> Devise inconnue. </td></tr>
<tr><td> 00011 </td><td> Montant incorrect. </td></tr>
<tr><td> 00015 </td><td> Paiement déjà effectué. </td></tr>
<tr><td> 00016 </td><td> Abonné déjà existant (inscription nouvel abonné). Utilisation de la valeur ‘U’ dans la variable PBX_RETOUR. </td></tr>
<tr><td> 00021 </td><td> Carte non autorisée. </td></tr>
<tr><td> 00029 </td><td> Carte non conforme. Code erreur renvoyé lors de la documentation de la variable « PBX_EMPREINTE ». </td></tr>
<tr><td> 00030 </td><td> Temps d’attente supérieur au délai maximal par l’internaute/acheteur au niveau de la page de paiements. Délais de 15 min par défaut, ou définit dans la variable PBX_DISPLAY </td></tr>
<tr><td> 00031 </td><td> Réservé </td></tr>
<tr><td> 00032 </td><td> Réservé </td></tr>
<tr><td> 00033 </td><td> Code pays de l’adresse IP du navigateur de l’acheteur non autorisé. </td></tr>
<tr><td> 00040 </td><td> Opération sans authentification 3-D Secure, bloquée par le filtre. </td></tr>
<tr><td> 99999 </td><td> Opération en attente de validation par l’émetteur du moyen de paiement. </td></tr>
</table>

**PBX_HASH**

L'algorithme doit être choisi parmi la liste suivante :

* SHA512 (par défaut)
* SHA256
* SHA384
* SHA224
* RIPEMD160
* MDC2 (pas présent sur mes serveurs)

####Forçage du type et moyen de paiement

Il est possible de fournir au moment de l'appel à Paybox System les 2 variables suivantes (l'une ne va pas sans l'autre) :

* PBX_TYPEPAIEMENT
* PBX_TYPECARTE

####Authentification du message par empreinte

1. Concaténer l'ensemble des variables en séparant chaque variable par le symbole & . L'ordre des variables doit être le même que dans le formulaire ou la requête CURL. Dans cette chaine, les données ne sont pas URL-encodées.
2. Lancer le calcul de l'empreinte en utilisant la chaine ainsi concaténée, la clé secrète obtenue par le BackOffice, l'algorithme indiqué dans PBX_HASH.
3. Le résultat obtenu doit être placé dans la variable PBX_HMAC

Exemple :

    <?php
    // On récupère la date au format ISO-8601
    $dateTime = date("c");
    // On crée la chaîne à hacher sans URLencodage
    $msg = "PBX_SITE=1999888".
    "&PBX_RANG=32".
    "&PBX_IDENTIFIANT=2".
    "&PBX_TOTAL=".$_POST['montant'].
    "&PBX_DEVISE=978".
    "&PBX_CMD=".$_POST['ref'].
    "&PBX_PORTEUR=".$_POST['email'].
    "&PBX_RETOUR=Mt:M;Ref:R;Auto:A;Erreur:E".
    "&PBX_HASH=SHA512".
    "&PBX_TIME=".$dateTime;
    // On récupère la clé secrète HMAC (stockée dans une base de données par exemple) 
    // et que l’on renseigne dans la variable $keyTest;
    // Si la clé est en ASCII, On la transforme en binaire
    $binKey = pack("H*", $keyTest);
    // On calcule l’empreinte (à renseigner dans le paramètre PBX_HMAC) grâce à la 
    // fonction hash_hmac et la clé binaire. On envoie via la variable PBX_HASH 
    // l'algorithme de hachage qui a été utilisé (SHA512 dans ce cas)
    // Pour afficher la liste des algorithmes disponibles sur votre environnement, 
    // décommentez la ligne suivante
    // print_r(hash_algos());
    $hmac = strtoupper(hash_hmac('sha512', $msg, $binKey));
    // La chaîne sera envoyée en majuscules, d'où l'utilisation de strtoupper()
	 // On crée le formulaire à envoyer à Paybox System
	 // ATTENTION : l'ordre des champs est extrêmement important, il doit
	 // correspondre exactement à l'ordre des champs dans la chaîne hachée
	 ?>
	 <form method="POST" action="https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi">
	 <input type="hidden" name="PBX_SITE" value="1999888">
	 <input type="hidden" name="PBX_RANG" value="32">
	 <input type="hidden" name="PBX_IDENTIFIANT" value="2">
	 <input type="hidden" name="PBX_TOTAL" value="<? echo $_POST['montant']; ?>">
	 <input type="hidden" name="PBX_DEVISE" value="978">
	 <input type="hidden" name="PBX_CMD" value="<? echo $_POST['ref']; ?>">
	 <input type="hidden" name="PBX_PORTEUR" value="<? echo $_POST['email']; ?>">
	 <input type="hidden" name="PBX_RETOUR" value="Mt:M;Ref:R;Auto:A;Erreur:E">
	 <input type="hidden" name="PBX_HASH" value="SHA512">
	 <input type="hidden" name="PBX_TIME" value="<? echo $dateTime; ?>">
	 <input type="hidden" name="PBX_HMAC" value="<? echo $hmac; ?>">
	 </form>
	 
####URL appelée

Il est conseillé de tester avant l'appel la disponibilité des serveurs en essayant d’accéder à une page HTML « load.htm ». Cette page contient uniquement la chaîne « OK » qui confirme que le serveur est accessible. Voici le code pour faire ce test :

	<?php
	$serveurs = array('tpeweb.paybox.com', //serveur primaire
	'tpeweb1.paybox.com'); //serveur secondaire
	$serveurOK = "";
	foreach($serveurs as $serveur){
	  $doc = new DOMDocument();
	  $doc->loadHTMLFile('https://' . $serveur . '/load.html');
	  $server_status = "";
	  $element = $doc->getElementById('server_status');
	  if($element){
		 $server_status = $element->textContent;
	  }
	}

###Gestion de la réponse

On gère de façon automatique la validation des bons de commande en analysant le résultat de la transaction par l'intermédiaire de l'URL nommée **IPN** (Instant Payment Notification)

Une fois le paiement réalisé, le client peut revenir sur School Bus Manager par l'intermédiaire du 4 URL.

####Redirection du client

On peut avoir 4 URL selon que le paiement est accepté, refusé, annulé ou en attente. Pour cela, on doit passer 4 variables :

* PBX_EFFECTUE, 
* PBX_REFUSE, 
* PBX_ANNULE, 
* PBX_ATTENTE

Ces URL auraient pu être inscrites dans Paybox System par le backoffice. Ce n'est pas le cas dans la configuration de TRANSDEV. Aussi, **ces 4 URL seront placées dans le fichier config/paybox.config.php**

_Pour le moment, ces 4 URL seront configurées sur la page d'accueil de l'espace Parents. Par la suite, une amélioration serait de créer 4 méthodes dans le controller SbmParent\Controller\Index qui n'auraient qu'à lancer un flashMessenger avec le compte-rendu de l'opération de paiement puis réorienter sur l'accueil de l'espace Parents. Malheureusement, il faudrait savoir revenir sur la page de l'application déjà ouverte dans le navigateur. A étudier._

Il est prudent de URL-encoder ces URLs.

####Validation des bons de commande

#####Principe du IPN (Instant Payment Notification)

Ce paramètre IPN est spécialement utilisé pour gérer de façon automatique la validation des bons de commandes.

Ce paramètre est une URL enregistrée dans la base de données Paybox mais elle peut également être gérée dynamiquement comme les 4 URL précédentes via la variable « PBX_REPONDRE_A ».

L’avantage de cette URL est qu’elle est appelée de serveur à serveur dès que le client valide son paiement (que ce dernier soit autorisé ou refusé). Cet appel ne transite pas par le navigateur du client.

A cette adresse, un script va recevoir et analyser la notification envoyée par Paybox. Les seules limitations sont :

* __ne pas faire de redirection__
* __doit générer une page vide__

L’URL précisée dans le paramètre IPN est appelée à chaque tentative de paiement - dans la limite de 3 tentatives maximum.

#####Paramètres
Lors de l'appel au paiement, on configure la liste des variables qui devront être renvoyées au site Marchand dans les différentes URL de retour au moyen de la variable PBX_RETOUR, qui se configure en concaténant la liste des informations souhaitées sous le format suivant :

	<nom de la variable que vous souhaitez>:<lettre Paybox correspondante>;
Exemple :
	
	ref:R;trans:T;auto:A;tarif:M;abonnement:B;pays:Y;erreur:E
Le nom des variables (montant, maref,…) est personnalisable. Pour voir l’ensemble des données disponibles, voir le paramètre PBX_RETOUR.

Ces informations seront envoyées à toutes les URL de retour (PBX_EFFECTUE, PBX_ANNULE, PBX_REFUSE, PBX_ATTENTE et PBX_REPONDRE_A). Par exemple, pour l’URL IPN, avec la valeur citée ci-dessus, la page appelée serait :

	http://www.commerce.fr/cgi/verif_pmt.asp?ref=abc12&trans=71256&auto=30258&tarif=2000&abonnement=354341&pays=FRA&erreur=00000
Cet appel est par défaut effectué via la méthode « GET ». Si la méthode « POST » est préférée pour le transfert des paramètres, il faut l’indiquer dans la variable PBX_RUF1.

#####Gestion des erreurs
Si une erreur se produit lors de l’appel de l’URL IPN, un mail d’avertissement sera envoyé sur la même adresse mail utilisée pour envoyer les tickets de paiements. Par exemple, si l’URL d’appel est :

	http://www.commerce.fr/cgi/verif_pmt.asp?ref=abc12&trans=71256&auto=30258&tarif=2000&abonnement=354341&pays=FRA&erreur=00000
Le message d’erreur reçu sera le suivant :

	Objet : PAYBOX: WARNING!!
	Corps du message :
	WARNING: Impossible de joindre http://www.commerce.fr pour le paiement ref=abc12&trans=71256&auto=30258&tarif=2000&abonnement=354341&pays=FRA&erreur=00000 Erreur : code HTTP: 302
	
A la fin de ce message sont précisées dans un libellé spécifique des informations permettant de comprendre la cause de l’erreur :

<ul><li> Le libellé « Erreur : code HTTP: XXX » fait référence au code retour du protocole HTTP
  <ul><li> Voir la liste des codes retour HTTP en §12.2 Codes retour HTTP </li>
      <li> Seuls les codes retour commençant par un 2 sont considérés comme valides. </li></ul>
</li>      
<li> Dans le cas d’une erreur CURL, l’erreur obtenue sera présentée dans le mail, pour une erreur CURL 28 on obtiendra par exemple :
  <ul><li>  Erreur : Operation timed out after 20000 milliseconds with 0 bytes received </li>
	<li> Voir la liste des codes retour CURL en §12.3 Codes erreur CURL </li></ul>
</li></ul>

#####Vérification des valeurs
L’IPN est appelée quel que soit le résultat du paiement (accepté ou refusé).
Comme tous les messages et signatures transportés au moyen du protocole HTTP (GET ou POST), l’IPN est URL-encodé.
Il faut donc URL décoder le message transmis.

Pour connaître le résultat du paiement, il est indispensable de vérifier le contenu des variables suivantes :

<ul><li><b>  Numéro d’autorisation (A)  :</b> alphanumérique, longueur variable.
  <ul><li>  Pour une transaction de test (pas de demande d’autorisation vers le serveur de la banque ou l’établissement financier privatif), la variable vaut toujours « XXXXXX »
      <li>  Pour une transaction refusée, la variable n’est pas envoyée
</li></ul>
<li><b> Code erreur (E) :</b>
	<ul><li>  Pour une transaction valide, il doit être à « 00000 »</li>
	<li> Pour les autres valeurs, se reporter au §11.1.7 Tableau 3 : Codes réponse PBX_RETOUR </li></ul>
</ul>
Pour s’assurer que la réponse provient bien de Verifone, il est fortement conseillé de vérifier le contenu des variables suivantes :

<ul><li><b>  Signature Paybox (K) :</b> Voir paragraphe ci-dessous </li>
<li><b> Adresse IP d’origine :</b> Pour améliorer la sécurité, il est possible de vérifier que l’appel de l’URL IPN provient bien d’un des serveurs Verifone (voir §12.6 URL d’appel et Adresses IP). </li></ul>

Pour que le paiement soit assuré il faut vérifier :

* que le numéro d'autorisation existe
* que le code erreur est à zéro
* que le montant est identique au montant origine
* que la signature électronique est vérifiée

Dans le cas d’un paiement refusé par le centre d’autorisation (code erreur à 001xx), les « xx » représentent le code renvoyé par le centre. Ce code permet de connaître la raison exacte du rejet de la transaction.
Par exemple, pour une transaction refusée pour raison « provision insuffisante », le code erreur renvoyé sera 00151.

Tous les codes sont précisés en §12.1 Codes réponses du centre d’autorisation

#####Signature Verifone
En utilisant la signature Verifone dans les variables à retourner vers les URL du site Marchand, ce dernier peut s’assurer que :

* les données renvoyées n’ont pas été altérées,
* c’est bien Verifone qui effectue un appel des URL du site.

Il est important de noter que la donnée K de la variable « PBX_RETOUR » doit être toujours être située en dernière position. Par exemple :

* _PBX_RETOUR=montant:M;auto:A;idtrans:S;sign:K_  est correcte
* _PBX_RETOUR=montant:M;auto:A;sign:K;idtrans:S_  est incorrecte

La clé publique de Verifone est en libre téléchargement depuis le site www.paybox.com à la rubrique « Espace intégration » puis « Manuels et documentations ». Pour être en conformité avec les règles de sécurité, Verifone est susceptible de changer sa paire de clé publique/privée : il doit donc être possible de mettre en place différentes clés publiques au niveau des serveurs Marchand.

La signature Verifone est produite en chiffrant un condensé SHA-1 avec une clé privée RSA. La taille d'une empreinte SHA-1 étant de 160 bits et la clé Verifone faisant 1024 bits de long, la signature est toujours une valeur binaire de taille [fixe] 128 octets (172 octets en Base64).

Pour la vérification, il faut utiliser la fonction 'openssl_verify()'.

Les messages et signatures transportés au moyen du protocole HTTP (GET ou POST) étant sur-encodés (URL encodage et/ou Base64),  il faut procéder aux opérations inverses avant de vérifier la signature :

1. détacher la signature du message,
2. URL décoder la signature,
3. décodage Base64 de la signature,
4. vérification de la signature [binaire] sur les données (toujours encodées)

Avec l’URL IPN de notification (paramètre PBX_REPONDRE_A), la signature électronique s’effectue uniquement par rapport au contenu de la variable PBX_RETOUR contrairement aux quatre autres URL où la signature est calculée sur l’ensemble des variables.

######Données signées :
1. lors de la réponse Verifone de serveur à serveur (URL IPN), seules les informations demandées dans la variable PBX_RETOUR sont signées,
2. dans les 4 autres cas (redirection via le navigateur du client, PBX_EFFECTUE, PBX_REFUSE et PBX_ANNULE, PBX_ATTENTE), ce sont toutes les données suivant le ' ? ' (les paramètres URL).

Exemple : 

	http:// www.moncommerce.com /mondir/moncgi.php ? monparam=mavaleur& pbxparam1=val1&pbxparam2=val2 ... &sign=df123dsfd3...1f1ffsre%20t321rt1t3e= 
La signature (df123dsfd3...1f1ffsre%20t321rt1t3e=) porte sur la partie :
1. pbxparam1=val1&pbxparam2=val2 ...
2. monparam=mavaleur& pbxparam1=val1&pbxparam2=val2 ...

Rappel : si la signature n'est pas la dernière valeur demandée dans la liste PBX_RETOUR, les valeurs suivantes seront retournées, mais pas signées.

######Signature non vérifiée :
Si une signature ne peut être vérifiée, alors les cas suivants doivent être envisagés :

* erreur technique : bogue, environnement cryptographique mal initialisé ou mal configuré, ...
* utilisation d'une clé erronée
* données altérées ou signature contrefaite.

Le dernier cas est peu probable, mais grave. Il doit conduire à la recherche d'une intrusion dans les systèmes d'informations impliqués.

##Option gestion des abonnements

###Principe

La gestion des paiements par abonnement permet au commerçant de gérer des prélèvements périodiques ou des paiements en plusieurs fois pour ses clients. Ainsi, une fois le paiement initial effectué, le client sera prélevé de façon cyclique suivant une fréquence choisie préalablement par le commerçant.

* La gestion de l’abonnement sur Paybox System est une gestion de base : elle ne prévoit que des cas simples d’abonnements, basés sur la reconduction périodique de paiement d’une même somme, sur une période souhaitée initialement par le commerçant. Ces paramètres ne peuvent pas, par la suite, être modifiés.
* Malgré sa simplicité, le système offre une souplesse de paramétrage permettant notamment, avec la gestion des différés, un large éventail de déclenchement de la première reconduction de l’abonnement.
* __Il est à noter qu’en cas d’échec (refus d’autorisation) sur une échéance, Verifone n’assure pas de représentation et stoppe les futures échéances.__ (La solution Paybox Direct Plus apporte plus de souplesse sur ce sujet).
* Le commerçant peut suivre ses abonnements via son accès au Back Office Commerçant

Pour gérer cette option il faut modifier le contenu de la variable PBX_CMD comme expliqué dans le paragraphe ci-dessous.

###Création d'un abonnement

La gestion d'un abonnement s'effectue via des sous-variables concaténées à la fin de la variable PBX_CMD (et sans séparateur). La taille des variables doit être respectée et le nom des variables est en majuscules à prendre dans le tableau suivant :
<table>
<tr><th> NOM </th><th> DESCRIPTION </th><th> TAILLE </th></tr>
<tr><td> PBX_2MONT </td><td> Montant des prochains prélèvements en centimes (0 = montant identique au paiement initial précisé dans PBX_TOTAL) </td><td> 10 chiffres </td></tr>
<tr><td> PBX_NBPAIE </td><td> Nombre de prélèvements (0 = toujours) </td><td> 2 chiffres </td></tr>
<tr><td> PBX_FREQ </td><td> Fréquence des prélèvements en mois </td><td> 2 chiffres </td></tr>
<tr><td> PBX_QUAND </td><td> Jour du mois auquel le prélèvement sera effectué (0 = le même jour que le paiement initial) </td><td> 2 chiffres </td></tr>
<tr><td> PBX_DELAIS </td><td> Nombre de jours d’attente avant le déclenchement du début de l’abonnement </td><td> 3 chiffres </td></tr>
</table>
_Les valeurs doivent être complétées à gauche par des zéros si nécessaire afin de respecter la taille._

####Exemples d’abonnement:

#####Exemple 1 :
Si le paiement initial (15 euros, soit 1500 centimes) est effectué le 28 novembre, le premier prélèvement aura lieu le 03 décembre (car la prise en compte de l’abonnement se fait 5 jours plus tard via PBX_DELAIS).

Tous les prélèvements sont d’un montant de 5 euros (soit 500 centimes) (PBX_2MONT), réalisés le 28 (PBX_QUAND) de tous les mois (PBX_FREQ) jusqu’à une demande de résiliation (PBX_NBPAIE) de votre part ou un rejet du centre d’autorisation (si la carte bancaire est arrivée à expiration).

	PBX_SITE=1999888&PBX_RANG=99&PBX_IDENTIFIANT=2&PBX_TOTAL=1500&PBX_DEVISE=978&PBX_CMD=ma_ref123PBX_2MONT0000000500PBX_NBPAIE00PBX_FREQ01PBX_QUAND28PBX_DELAIS005&PBX_PORTEUR=test@paybox.com&PBX_RETOUR=Mt:M;Ref:R;Auto:A;Erreur:E&PBX_HASH=SHA512&PBX_TIME=2015

_Détail de PBX_CMD_ : PBX_CMD=ma_ref123PBX_2MONT0000000500PBX_NBPAIE00PBX_FREQ01PBX_QUAND28PBX_DELAIS005

#####Exemple 2 :
Si le paiement initial (15 euros) est effectué le 28 novembre, le premier prélèvement aura lieu le 31 novembre (car la prise en compte de l’abonnement est immédiate via PBX_DELAIS qui est inexistante).

10 prélèvements (PBX_NBPAIE) d’un montant de 5,50 euros (PBX_2MONT) seront réalisés tous les 3 mois (PBX_FREQ) le dernier jour du mois (PBX_QUAND).

	PBX_SITE=1999888&PBX_RANG=99&PBX_IDENTIFIANT=2&PBX_TOTAL=1500&PBX_DEVISE=978&PBX_CMD=ma_ref123PBX_2MONT0000000550PBX_NBPAIE10PBX_FREQ03PBX_QUAND31&PBX_PORTEUR=test@paybox.com&PBX_RETOUR=Mt:M;Ref:R;Auto:A;Erreur:E&PBX_HASH=SHA512&PBX_TIME=2015-11-28T11:01:50+01:00
	
_Détail de PBX_CMD_ : PBX_CMD=ma_ref123PBX_2MONT0000000550PBX_NBPAIE10PBX_FREQ03PBX_QUAND31

##URL et adresses IP

###URL d'appel
<table>
<tr><th> PLATE-FORME    </th><th> URL D’ACCÈS </th></tr>
<tr><td> Pré-production </td><td> https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi </td></tr>
<tr><td> Principal      </td><td> https://tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi         </td></tr>
<tr><td> Secondaire     </td><td> https://tpeweb1.paybox.com/cgi/MYchoix_pagepaiement.cgi        </td></tr>
</table>

###Sécurité par contrôle de l'adresse IP

L’adresse IP sortante est l’adresse avec laquelle le site Marchand verra arriver les flux de retour en fin de transaction (appels de l’IPN par exemple).
<table>
<tr><th> PLATE-FORME </th><th> ADRESSE SORTANTE </th></tr>
<tr><td> Pré-production </td><td> 195.101.99.76 </td></tr>
</table>

<table>
<tr><th> PLATE-FORME </th><th> ADRESSE SORTANTE </th></tr>
<tr><td> Production </td><td> 194.2.122.158 </td></tr>
<tr><td>            </td><td> 194.2.122.190 </td></tr>
<tr><td>            </td><td> 195.25.7.166 </td></tr>
<tr><td>            </td><td> 195.25.67.22 </td></tr>
</table>
