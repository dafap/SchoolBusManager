# Présentation d'un document pdf

## Les types de présentation

Chaque document est constitué de 3 sections :
* l'en-tête de document
* le corps du document
* le pied de document

Chaque section peut définir :
* un haut de page
* un corps de page
* un bas de page

Le module prévoit 3 types de présentation :
* présentation tabulaire
* présentation verticale
* présentation en fiche
mais le code n'est pas encore implémenté. 

Pour le moment, il n'y a qu'un modèle par défaut, permettant de constituer un document contenant des données en tableau.

Par la suite, pour les étiquettes et les pages publipostées, il faudra écrire des méthodes comme le décrit la suite de cette documentation.

## En-tête de document
L'en-tête de document sera présent si la case _Y a-t-il une en-tête de document ?_ est cochée.

On précise alors s'il y a un saut de page après l'en-tête de document, s'il y a un en-tête de page sur le première page ... 
(voir la section **En-tête de document** du formulaire de configuration d'un document).

La constitution de l'en-tête est définie le _Modèle de page d'en-tête_ choisi. Par défaut, il n'y a qu'un seul modèle.

### Ajouter un modèle de page d'en-tête
L'en-tête par défaut est décrit dans _templateDocHeaderMethod1()_. 
Pour créer un nouveau modèle d'en-tête, il faut écrire une méthode _templateDocHeaderMethod_**x** où **x** est un entier de sorte que les 
méthodes _templateDocHeaderMethod_**x** soient numérotées consécutivement à partir de 1.

Prendre exemple sur le modèle par défaut.

## Corps de document
Le corps de document par défaut est décrit dans la méthode _templateDocBodyMethod1()_.
Pour créer un nouveau modèle de corps de document, il faut écrire une méthode _templateDocBodyMethod_**x** où **x** est un entier de sorte que
les méthodes _templateDocBodyMethod_**x** soient numérotées consécutivement à partir de 1.

Le modèle par défaut définit un document contenant un tableau de données.

## Pied de document
Le pied de document sera présent si la case _Y a-t-il un pied de document ?_ est cochée.

On précise alors les autres caractéristiques du pied de document dan la section **Pied de document** du formulaire de configuration d'un document.

La méthode par défaut est _templateDocFooterMethod1()_. Elle définit les variables 
* %nombre% qui donne le nombre de lignes dans le corps du document, 
* %numero% qui donne le numéro de la source lorsqu'il y a plusieurs sources dans le même document.

## En-tête de page
Sur le même principe, les en-têtes de pages sont dessinées à partir de modèles définis par les méthodes _templateHeaderMethod_**x**. 
L'en-tête par défaut est définie par la méthode _templateHeaderMethod1()_. On peut créer de nouveaux en-têtes en écrivant de nouvelles méthodes.

## Pied de page
Toujours sur le même principe, les pieds de pages sont dessinées à partir de modèles définis par les méthodes _templateFooterMethod_**x**. 
Le pied de page par défaut est définie par la méthode _templateFooterMethod1()_. On peut créer de nouveaux pieds de page en écrivant de nouvelles méthodes.

Le pied de page par défaut définit les variables :
* %date% : date courante de création du document
* %nombre% : nombre de lignes de données dans cette page
* %somme(colonne)% où colonne est le rang de la colonne surlaquelle porte la somme (à partir de 1)
* %max(colonne)% qui donne la valeur maximale de la colonne
* %min(colonne)% qui donne la valeur minimale de la colonne
* %moyenne(colonne)% qui donne la moyenne des valeurs de la colonne

