#Documentation concernant les mails

## Concernant la casse dans les adresses mail
La RFC 5322 décrit les règles pour une adresse de la forme : `partie locale@domaine`

* la partie locale est sensible à la casse
* le domaine est insensible à la casse

## Pour obtenir une confirmation de mail

### ajouter une clé dans l'entête
La demande de confirmation d'un envoi de mail n'est pas complètement standardisée et ne donne pas un résultat de confiance absolue.

On demande une confirmation en ajoutant une clé dans l'entête du mail. Pour cela, avex zendframework :

    $message->getHeaders()->addHeaderLine('clé', $email)->addHeaderLine(...)
    // autant qu'il faudra de clés dans le header

En effet, il y a de nombreuses clés à rajouter et laplupart ne sont pas standards.

Les clés standards sont :

    'Disposition-Notification-To'
    'Generate-Delivery-Report'
    
Les autres clés sont :

    'X-Confirm-Reading-To'
    'Read-Receipt-To'
    'Return-Receipt-Requested'
    'Return-Receipt-To'
    'Registred-Mail-Reply-Requested-By'
    
A savoir que, pour utiliser `'Disposition-Notification-To'`, le mail à passer en second paramètre de la méthode `addHeaderLine()` doit être le même que le mail paramétré dans le FROM du SMTP. Il est présent dans l'entête sous la clé `'Return_Path'`. Il faut aussi qu'une clé `'MessageId'` soit présente.

Les références :

* `'Content-Retrun'` : RFC 2156 (not for general usage) indique si le contenu d'un message doit être renvoyé avec des notifications de non remise.
* `'Disposition-Notification-To'` : RFC 8098 indique que l'expéditeur souhaite une notification de disposition à la réception par les expéditeurs.
* `'Generate-Delivery-Report'` : RFC 2156 (not for general usage) indique si un rapport de livraison est souhaité lors d'une livraison réussie. Par défaut, il n'y a pas de rapport.
* `'Disposition-Notification-Options'` RFC 8098 est réservé pour passer des options à la clé précédente.
* `'X-Confirm-Reading-To'` et les autres ... ne sont pas standard mais certains sont largement utilisés.

La syntaxe pour `'Disposition-Notification-To'` :

Dans le header, suivre ce modèle : `mdn-request-header="Disposition-Notification-To""."MAILBOX_LIST[CRLF]`
Cette clé ne peut être présente qu'au plus 1 fois.

### Avec un compteur sur une image

On peut également ajouter dans le mail une image dont l'adresse déclenche un compteur. Par exemple :

    <img src="https://www.dafap.fr/moncompteur.php?id=<?= $destinataire;?>" width="1" height="1">
    
où le script `moncompteur.php` enregistre les paramètres passés et renvoie une image transparente. Par la suite, un tableau de bord permet d'analyser ces données afin d'en faire un rapport. 

Cela semble sympa mais de nombreux lecteurs de courrier n'affichent les images qu'à la demande. Aussi, cette méthode n'est pas plus fiable que la première.