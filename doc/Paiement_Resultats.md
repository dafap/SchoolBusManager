#Documentation de la classe SbmCommun\Model\Paiements\Resultats

##Liste des méthodes publiques et usage

### equalTo(Resultats $r) :
1. compare ce Resultats ($this) à un autre ($r) sans tenir compte des paiements
2. utilisée dans 
  
### getAbonnements(string $nature) :	
1. selon la nature (tous, inscrits, liste) renvoie la structure précisée avec DETAIL_ABONNEMENTS et MONTANT_ABONNEMENTS
2. utilisée dans	
    * SbmAjax\Controller\FinanceController::checkpaiementscolariteAction()    
    * SbmAjax\Controller\FinanceController::uncheckpaiementscolariteAction()
    
### getAbonnementsDetail($nature) :	
1. selon la nature (tous, inscrits, liste) renvoie un tableau associatif où les clés sont grilleCode. Chaque enregistrement du tableau est un tableau associatif présentant les clés 'grille', 'quantite' et 'montant'						
2. utilisée dans	
    * self::signature()
    * self::equalTo()
    * module/SbmGestion/view/sbm-gestion/finance/paiement-detail.phtml
    * module/SbmPaiement/Plugin/PayBox/view/formulaire.phtml
    * module/SbmPdf/view/sbm-pdf/layout/facture.phtml

### getAbonnementsMontant($nature) :	
1. selon la nature (tous, inscrits, liste) renvoie le montant total des abonnements ou 0
2. utilisée dans	
     * module/SbmGestion/view/sbm-gestion/finance/paiement-detail.phtml
     * module/SbmPaiement/Plugin/PayBox/view/formulaire.phtml
     * module/SbmPdf/view/sbm-pdf/layout/facture.phtml
										
### getArrayEleveId() :	
* utilisée nulle part

### getDuplicatas() : 	
1. LE NOM EST TROMPEUR !!! Renvoie la structure décrite dans le constructeur. Voir aussi getMontantDuplicatas()
2.utilisée nulle part 

### getListeEleves($nature) :	
1. selon la nature (tous, liste) renvoie un tableau associatif dont les clés sont eleveId et qui pour chaque enregistrement présente un tableau associatif avec les clés nom, prenom, grilleCode, grilleTarif, duplicatas, paiements
2. utilisée dans	
    * self::signature()
    * self::equalTo()
    * module/SbmGestion/view/sbm-gestion/finance/paiement-detail.phtml
    * module/SbmPaiement/Plugin/PayBox/view/formulaire.phtml
	 * SbmPaiement\Plugin\PayBox\prepare()
	 * module/SbmPdf/view/sbm-pdf/layout/facture.phtml

### getMontantDuplicatas($nature) :	
1. selon la nature (tous, liste) renvoie le montant total des duplicatas (float)
2. utilisée dans	
     * SbmAjax\Controller\FinanceController::checkpaiementscolariteAction()
     * self::equalTo()
     * self::signature()
     * module/SbmGestion/view/sbm-gestion/finance/paiement-detail.phtml
     * module/SbmPaiement/Plugin/PayBox/view/formulaire.phtml
     * module/SbmPdf/view/sbm-pdf/layout/facture.phtml

### getMontantTotal($nature) :	
1. selon la nature (tous, liste) renvoie le montant total (float)
2. utilisée dans	
     * SbmAjax\Controller\FinanceController::calculmontantAction()
     * SbmCommun\Model\Paiements\Facture::add()
     * self::equalTo()
     * self::getSolde()
     * self::signature()
     * module/SbmGestion/view/sbm-gestion/finance/paiement-detail.phtml
     * module/SbmGestion/view/sbm-gestion/finance/paiement-liste.phtml
     * module/SbmPaiement/Plugin/PayBox/view/formulaire.phtml
     * module/SbmParent/view/sbm-parent/index/index.phtml
     * module/SbmPdf/view/sbm-pdf/layout/facture.phtml

### getPaiements() :
1. renvoie la structure des paiements
2. utilisée nulle part 

### getPaiementsDetail() :	
1. renvoie la branche DETAIL_PAIEMENTS de la structure des paiements
2. utilisée dans	
     * module/SbmGestion/view/sbm-gestion/finance/paiement-detail.phtml
     * module/SbmPdf/view/sbm-pdf/layout/facture.phtml

### getPaiementsMontant() :	
1. renvoie le total des paiements (float) contenu dans la branche MONTANT_PAIEMENTS de la structure des paiements
2. utilisée dans	
     * SbmAjax\Controller\FinanceController::checkpaiementscolariteAction()
     * SbmAjax\Controller\FinanceController::calculmontantAction()
     * module/SbmGestion/view/sbm-gestion/finance/paiement-detail.phtml
     * module/SbmGestion/view/sbm-gestion/finance/paiement-liste.phtml
     * module/SbmPaiement/Plugin/PayBox/view/formulaire.phtml
     * module/SbmParent/view/sbm-parent/index/index.phtml
     * module/SbmPdf/view/sbm-pdf/layout/facture.phtml
### getResponsableId() :	
* utilisée dans	
     * SbmCommun\Model\Paiements\Facture::__construct()
     * SbmCommun\Model\Paiements\Facture::facturer()
     * SbmCommun\Model\Paiements\Facture::lire()
     * SbmCommun\Model\Paiements\Facture::add()

### getSolde($nature) :	
1. selon la nature (tous, liste) renvoie la différence entre le montant total (getMontantTotal) et les paiements (getPaiementsMontant)
2. utilisée dans	
     * SbmAjax\Controller\FinanceController::calculmontantAction()
     * SbmGestion\Controller\FinancesController::paiementAjoutAction()
     * module/SbmGestion/view/sbm-gestion/finance/paiement-liste.phtml
     * SbmPaiement\Plugin\PayBox\prepareAppel()
     * module/SbmParent/view/sbm-parent/index/index.phtml
     * module/SbmPdf/view/sbm-pdf/layout/facture.phtml

### isEmpty() : 
1. renvoie VRAI s'il n'y a pas de responsableId
2. utilisée dans 
     * SbmCommun\Model\Paiements\Calculs::getResultats($responsableId, $arrayEleveId, $force)
     
### setAbonnementsDetail($nature, $abonnements) :	
1. contrôle de la structure des valeurs proposées par validAbonnementKey($nature) et validArrayAbonnement($abonnements)
2. utilisée dans 
     * SbmCommun\Model\Paiements\Calculs::appliquerGrilleTarif($nature, \Zend\Db\Sql\Select $select)
      
### setAbonnementsMontant($nature, $montant) :	
1. contrôle de la structure des valeurs proposées par validAbonnementKey($nature)
2. utilisée dans 
      * SbmCommun\Model\Paiements\Calculs::appliquerGrilleTarif($nature, \Zend\Db\Sql\Select $select)
      
### setArrayEleveId($arrayEleveId) :
* utilisée dans 
     * SbmCommun\Model\Paiements\Calculs::analyse($responsableId, $arrayEleveId)
     
### setListeEleves($nature, $listeEleves) :	
1. contrôle de la structure des valeurs proposées par validNatureKey($nature) et validListeEleves($listeEleves)
2. utilisée dans 
      * SbmCommun\Model\Paiements\Calculs::compterDuplicatas($nature, \Zend\Db\Sql\Select $select)

### setMontantDuplicatas($nature, $montantDuplicatas) :	
1. contrôle les valeurs proposées par validNatureKey($nature)
2. utilisée dans 
      * SbmCommun\Model\Paiements\Calculs::compterDuplicatas($nature, \Zend\Db\Sql\Select $select)

### setPaiementsDetail($liste) :	
* utilisée dans 
       * SbmCommun\Model\Paiements\Calculs::calculPaiementsResponsable($responsableId)
              
### setPaiementsTotal($paiement) :
* utilisée dans 
        * SbmCommun\Model\Paiements\Calculs::calculPaiementsResponsable($responsableId)
        
### setResponsabeId($responsableId) :	
* utilisée nulle part 

### signature() :	
1. renvoie une chaine md5 d'une chaine composée de : 
      * millesime, responsableId, getMontantDuplicatas(), getMontantTotal()
      * puis pour chaque élève : eleveId, grilleCode, duplicatas, fa, gratuit (provenants de scolarites)
      * puis pour chaque abonnement : grilleCode, quantite, montant
2. utilisée dans SbmCommun\Model\Paiements\Facture::facturer() et SbmCommun\Model\Paiements\Facture::add()