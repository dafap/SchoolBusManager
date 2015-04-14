# Documentation du plugin SystemPay
===================================

## Référence
---------------

Ce plugin est basé sur le 

	Guide d'implementation du formulaire de paiement - Systempay 2.2 (Version du document 3.0)
	
Les documentations techniques de SystemPay se trouvent à 

	https://systempay.cyberpluspaiement.com/html/documentation.html
	
## Mise en oeuvre du formulaire de paiement
-------------------

Le POST permettant d'accéder au formulaire de paiement est créé par cURL. 

Etant donné que j'envoie vads\_cust\_email, la palteforme SystemPay enverra une facturette au client à cet email après son paiement.

Voici la liste des champs envoyés :

* vads\_site\_id,
* vads\_ctx\_mode,
* vads\_trans\_id,
* vads\_trans\_date,
* vads\_amount,
* vads\_currency,
* vads\_action\_mode,
* vads\_page\_action,
* vads\_version,
* vads\_payment\_config,
* vads\_capture\_delay,
* vads\_validation\_mode,
* vads\_cust\_email,
* vads\_cust\_id,
* vads\_cust\_first\_name,
* vads\_cust\_last\_name, 
* vads\_order\_id, 
* vads\_theme\_config,
* vads\_url\_success,
* vads\_url\_refused,
* vads\_url\_cancel,
* vads\_url\_error,
* vads\_url\_check,
* vads\_redirect\_success\_timeout,
* vads\_redirect\_success\_message,
* vads\_redirect\_error\_timeout,
* vads\_redirect\_error\_message,
* vads\_nb\_product,
* vads\_product\_ref1, 
* _éventuellement si nécessaire,_ vads\_product\_ref2, vads\_product\_ref3 ... 
* signature

Cette liste se retrouve dans la méthode _Plateforme::prepareAppel()_.

### Initialisation, formatage et correspondance
------------------

#### Initialisation dans SystemPay/config/systempay.config.php

* vads\_action\_mode,
* vads\_currency,
* vads\_page\_action,
* vads\_capture\_delay,
* vads\_validation\_mode,
* vads\_theme\_config,  
* vads\_version,

Ce fichier d'initialisation contient aussi un dispositif nécessaire au calcul d'un vads\_trans\_id unique :

* 'uniqid\_path' => realpath(\__DIR\__ . '../../../../../../../data/share'),
Attention, le dossier _data/share_ doit être autorisé en lecture-écriture.
* 'vads\_trans\_id\_max' => 1000

Ce dispositif est basé sur la taille d'un fichier uniqid.txt se trouvant dans ce répertoire. Le fichier est vidé lorsque sa taille dépasse vads\_trans\_id\_max. Il faut donc choisir un vads\_trans\_id\_max suffisant pour que la remise à 0 ne se fasse pas plus d'une fois par jour.

#### Initialisation dans config/autoload/sbm.local.php

* vads\_site\_id,

Ce fichier contient aussi les certificats permettant de calculer la signature en mode TEST et en mode PRODUCTION :

* 'certificat' => array(
            'TEST' => '1122334455667788',
            'PRODUCTION' => ''
        ),

#### Initialisation dans config/autoload/sbm.global.php

* vads\_ctx\_mode,
* vads\_url\_success,
* vads\_url\_refused,
* vads\_url\_cancel,
* vads\_url\_error,
* vads\_url\_check,
* vads\_redirect\_success\_timeout,
* vads\_redirect\_success\_message,
* vads\_redirect\_error\_timeout,
* vads\_redirect\_error\_message,

Ce fichier contient aussi les éléments suivants :

* 'authorized_ip' => array('194.50.38.0/24', '127.0.0.1'),
* 'error\_reporting' => Logger::INFO

Le niveau d'error\_reporting est basé sur les constantes de la classe Zend\Logger :

* DEBUG
* INFO
* NOTICE
* WARN
* ERR
* CRIT
* ALERT
* EMERG

#### Formatage de vads\_trans\_date

* 'YmdHis'

#### Formatage de vads\_amount

* En **centimes**. Il faut donc multiplier par 100 le montant reçu dans l'évènement.

#### Calcul de vads\_payment\_config

* Pour un paiement comptant : **SINGLE**
* Pour un paiement en plusieurs échéances : **MULTI:first=%d;count=%d;period=%d'** où
<ul><li>**first** correspond à la valeur en centimes de la première échéance (paiement immédiat)
<li>**count** correspond au nombre d'échéances
<li>**period** correspond au nombre de jours entre 2 échéances
</ul>

#### Format de vads\_order\_id

Cette référence est composée de :

* TS
* le millesime sur 4 caractères
* le séparateur -
* la date au format 'Ymd'
* le séparateur -
* le responsableId sur 11 caractères complété à gauche par des 0
* le séparateur -
* le nombre d'élèves concernés par ce paiement

#### Elèves concernés par ce paiement

Le nombre d'élèves concernés par ce paiement se trouve en dernière composante de vads\_order\_id.
Il se retrouve aussi dans vads\_nb\_product.

Les références des élèves concernés se trouvent dans :

* vads\_product\_ref1 _pour le 1er élève_
* vads\_product\_ref2 _pour le 2ème élève si nécessaire_
* vads\_product\_ref3 _pour le 3ème élève si nécessaire_
* ...

#### Références du responsable

* vads\_cust\_email : si ce champ est fourni, une facturette est adressé à cet email à la fin du paiement
* vads\_cust\_id : contient responsableId et sera renvoyé dans la notification,
* vads\_cust\_first\_name : contient le prénom du responsable et sera renvoyé dans la notification,
* vads\_cust\_last\_name : contient le nom du responsable et sera renvoyé dans la notification, 

## Traitement de la notification
------------------------------------

### Contrôle de l'origine de la notification

Seules les notifications provenant d'un serveur autorisé sont traités. Les autres sont historisées au niveau WARN.
Ce traitement est réalisé par la classe AbtractPlateforme.

### Contrôle de la notification

Ce contrôle est fait par la méthode Plateforme::validNotification().

Si le serveur est autorisé, un contrôle de signature est réalisé, puis la réponse du serveur est analysée afin de savoir si la requête a été comprise. 

Les erreurs de signature et les erreurs 30 et 96 sont historisées au niveau ERR.
Cela ne devrait pas se produire. Cela correspond à un dysfonctionnement du plugin.

### Contrôle du paiement

Ce contrôle est réalisé par la méthode validPaiement().

Je pars du principe qu'un paiement est réalisé si les conditions suivantes sont toutes remplies :

* vads\_result contient 00
* vads\_trans\_status contient AUTHORISED
* vads\_payment\_certificate est une chaine de 40 caractères

En cas de succès, la propriété error\_no est à 0 si la notification est nouvelle ou à 23000 s'il s'agit d'une notification déjà traitée. La a notification est historisée au niveau INFO avec comme message 'Paiement OK' ou 'Duplicate Entry' selon le cas.

En cas d'échec (abandon du client, refus de la banque ...), la propriété error\_message contient la raison de l'échec. La notification est historisée au niveau NOTICE avec comme message la raison de l'échec.

## Affichage de la liste des notifications reçues et du détail d'une notification

### La liste des notifications

La méthode **SbmPaiement\Controller\IndexController::listeAction()** permet d'afficher la liste des notifications répondant aux critères définis en bas de page. Cette liste est paginée (réglage dans _SbmPaiement\config\module.config.php_).

Pour connaitre les paramètres propres à la table du plugin nécessaires à la composition de la liste, certaines spécifications doivent être respectées :

* la classe _SystemPay\Table\TablePlugin_ **doit implémenter** l'interface _SbmPaiement\Plugin\TablePluginInterface_.
* les fichiers _liste.phtml_ et _voir.phtml_ **doivent se trouver** dans le dossier _SystemPay/view_.




