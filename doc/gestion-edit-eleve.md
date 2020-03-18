#Formulaire de modification d'un élève - rôle de gestion

##Principe général
Le formulaire est ouvert en modification 
* soit depuis une liste des élèves (elevelisteAction ou un groupAction sur une table)
* soit lors de la création d'un nouvel élève
* soit lors de la réinscription d'un ancien élève
Dans le premier cas, les paramètres sont passés en POST, dans les autres cas les paramètres sont passés à la méthode par son argument $arg.
Dans tous les cas, redirectToOrigin() est initialisé.

Il est nécessaire que  __$args['eleveId']__  soit disponible. Le formulaire est alors initialisé par les données concernant cet élève provenant des tables  __sbm_t_eleves__  et  __sbm_t_scolarites__ .

Les données concernant les responsables et celles concernant les affectations sur un circuit sont fournies par des appels en ajax une fois que le formulaire est affiché.

Lors de la validation du formulaire, les données validées sont enregistrées dans l'ordre suivant :

1. enregistrement dans la table sbm_t_eleves
1. enregistrements éventuels en cascade des changements de responsables dans la table sbm_t_affectations
1. enregistrement dans la table sbm_t_scolarites
1. calcul et enregistrement des distances
1. calcul des droits
1. détermination de la grille tarifaire et du droit à la réduction. Mais il est possible d'enregistrer des données différentes de celles calculées.

Par ailleur :

* Un double clic dans la case distance lance le calcul et l'enregistrement de la distance.
* Les affectations sont calculées et enregistrées par un appel ajax. Elles peuvent aussi être enregistrées manuellement à partir d'un formulaire.

##Structure du javascript de cette page
###Les objets JS

* js_edit : cette classe contient l'essentiel des méthodes
* affectation() : cette classe s'applique au formulaire AffectationDecision
* prisenechargepaiement() : cette classe s'applique au formulaire de choix de prise en charge du paiement
* stationdepart : cette classe s'applique au formulaire StationDepart et lance la recherche d'affectations automatique

###Liste des appels ajax

Dans js_edit :

* $("#eleve-etablissementId").change() : appel de la méthode getclassesforselectAction()
* $("#duplicatamoins").click() : appel de la méthode decrementeduplicataAction()
* $("#duplicataplus").click() : appel de la méthode incrementeduplicataAction()
* $("#eleve-accordR1").click() : appel de la méthoce checkaccordR1Action() ou uncheckaccordR1Action()
* $("#eleve-accordR2").click() : appel de la méthoce checkaccordR2Action() ou uncheckaccordR2Action()
* $("input[type='text'][name^='distanceR']").dblclick() : appel de la méthode donnedistanceAction()
* $("#eleve-responsable1Id").change() : appel de la méthode getresponsableAction()
* $("#eleve-responsable2Id").change() : appel de la méthode getresponsableAction()
* $("button[type=button][name=envoiphoto]").click() : appel de la méthode savephotoAction()
* $("button[type=button][name=supprphoto]").click() : appel de la méthode supprphotoActon()
* $("button[type=button][name=quartgauchephoto]").click() : applel de la méthode quartgauchephotoAction()
* $("button[type=button][name=quartdroitephoto]").click() : applel de la méthode quartdroitephotoAction()
* $("button[type=button][name=retournephoto]").click() : appel de la méthode retournephotoAction()
* $("#tabs").on('click',"i[data-button=btnaffectation]",function()) : appel de la méthode formaffectationAction()
* $("#tabs").on('click',"i[data-button=btnchercheraffectations]",function()) : appel de la méthode formchercheraffectationsAction()
* $("#eleve-btnpaiement").click() : appel de la méthode formpaiementAction()
* "init" : méthode publique - 2 appels à la méthode getresponsableAction()
* "majBlockAffectations" : appel aux méthodes blockaffectationsAction() et enableaccordbutton()

Dans affectaion()

* setStationsValueOptions() : appel de la méthode getstationsforselectAction()
 
