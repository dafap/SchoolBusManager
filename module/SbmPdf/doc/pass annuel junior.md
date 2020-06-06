# Principe
Les cartes seront adressées à l'organisme si l'élève dépend d'un organisme. Sinon elles seront adressées respectivement à chaque responsable de l'élève.

Les cartes sont triées par code postal, commune, nom du destinataire.

On pourra créer des lots différents pour les élèves ayant payer leur abonnement et ceux dont l'abonnement n'est pas payé.

# Présentation du modèle
Le modèle est composé de 4 cadres. L'origine est le point en haut à gauche du cadre (x ; y). Les dimensions sont (largeur ; hauteur). Toutes les dimensions sont en millimètres :

1. L'adresse : origine (95 ; 42) dimensions (100 ; 40)
2. Le numéro de la carte : origine (74 ; 216) dimensions (30 ; 4)
3. Le texte composant la carte : origine (19 ; 230) dimensions (58 ; 39)
4. La photo de la carte : origine (78 ; 230) dimensions (27 ; 33)

# Requêtes utilisées
## Version 2020
L'ordre programmé est : `adresseCommune,rNom,rPrenom`. Pas d'ordre pour les duplicatas.

La requête est la même pour la liste de contrôle, les étiquettes, les cartes et les duplicatas.

    SELECT
    ele.eleveId, sco.dateCarteR1, sco.dateCarteR2, sco.paiementR1, sco.paiementR2, sco.accordR1, sco.accordR2, sco.demandeR1, sco.demandeR2, sco.inscrit, sco.gratuit, sco.selection, sco.etablissementId, ele.numero,res.communeId,res.nomSA AS rNom,res.prenomSA AS rPrenom,
    IF(ISNULL(org.communeId),IF(aff.trajet=1 AND NOT ISNULL(sco.communeId),CONCAT('Mme ou M. ',sco.chez),CONCAT_WS(' ',res.titre,res.nom,res.prenom)),org.nom) AS responsable,
    IF(ISNULL(org.communeId),IF(aff.trajet=1 AND NOT ISNULL(sco.communeId),sco.adresseL1,res.adresseL1),org.adresse1) AS adresseL1,
    IF(ISNULL(org.communeId),IF(aff.trajet=1 AND NOT ISNULL(sco.communeId),sco.adresseL2,res.adresseL2),org.adresse2) AS adresseL2,
    IF(ISNULL(org.communeId),IF(aff.trajet=1 AND NOT ISNULL(sco.communeId),'',res.adresseL3),'') AS adresseL3,
    IF(ISNULL(org.communeId),IF(aff.trajet=1 AND NOT ISNULL(sco.communeId),CONCAT_WS(' ',sco.codePostal,comsco.`alias_laposte`),CONCAT_WS(' ',res.codePostal,comres.`alias_laposte`)),CONCAT_WS(' ',org.codePostal,comorg.`alias_laposte`)) AS adresseCommune,
    CONCAT_WS(' ',ele.nom,ele.prenom) AS eleve,
    CONCAT_WS(' ',eta.nom,cometa.aliasCG) AS ecole,
    IF(aff.trajet=1,CONCAT(sta1.nom,' (',comsta1.alias,')'),CONCAT(sta2.nom,' (',comsta2.alias,')')) AS station,
    matin,
    midi,
    soir,
    IF(ISNULL(photos.eleveId),FALSE,photos.type) AS typephoto,
    photos.dateExtraction AS dateExtraction,
    photos.photo AS photo
    FROM `sbm_t_eleves` AS ele
    JOIN `sbm_t_scolarites` AS sco ON sco.eleveId = ele.eleveId
    JOIN (SELECT millesime, eleveId, trajet, responsableId FROM `sbm_t_affectations` WHERE millesime=%millesime% GROUP BY millesime, eleveId, trajet, responsableId) AS aff ON sco.millesime=aff.millesime AND sco.eleveId=aff.eleveId
    JOIN `sbm_t_responsables`AS res ON res.responsableId=aff.responsableId
    JOIN `sbm_t_communes` AS comres ON comres.communeId=res.communeId
    JOIN `sbm_t_etablissements`AS eta ON sco.etablissementId=eta.etablissementId
    JOIN `sbm_t_communes` AS cometa ON cometa.communeId=eta.communeId
    JOIN `sbm_t_stations`AS sta1 ON sta1.stationId=sco.stationIdR1
    JOIN `sbm_t_communes` AS comsta1 ON comsta1.communeId=sta1.communeId
    LEFT JOIN `sbm_t_stations`AS sta2 ON sta2.stationId=sco.stationIdR2
    LEFT JOIN `sbm_t_communes` AS comsta2 ON comsta2.communeId=sta2.communeId
    LEFT JOIN sbm_t_elevesphotos photos ON photos.eleveId=ele.eleveId
    LEFT JOIN `sbm_t_communes` AS comsco ON comsco.communeId = sco.communeId
    LEFT JOIN `sbm_t_organismes` AS org ON org.organismeId=sco.organismeId
    LEFT JOIN `sbm_t_communes` AS comorg ON comorg.communeId=org.communeId
    LEFT JOIN (SELECT millesime, eleveId, trajet, GROUP_CONCAT(DISTINCT ligne1Id SEPARATOR '-') AS matin FROM `sbm_t_affectations` WHERE moment = 1 AND millesime=%millesime% GROUP BY millesime, eleveId, trajet) AS mat ON mat.millesime=aff.millesime AND mat.eleveId=aff.eleveId AND mat.trajet=aff.trajet
    LEFT JOIN (SELECT millesime, eleveId, trajet, GROUP_CONCAT(DISTINCT ligne1Id SEPARATOR '-') AS midi FROM `sbm_t_affectations` WHERE moment = 2 AND millesime=%millesime% GROUP BY millesime, eleveId, trajet) AS mid ON mid.millesime=aff.millesime AND mid.eleveId=aff.eleveId AND mid.trajet=aff.trajet
    LEFT JOIN (SELECT millesime, eleveId, trajet, GROUP_CONCAT(DISTINCT ligne1Id SEPARATOR '-') AS soir FROM `sbm_t_affectations` WHERE moment = 3 AND millesime=%millesime% GROUP BY millesime, eleveId, trajet) AS soi ON soi.millesime=aff.millesime AND soi.eleveId=aff.eleveId AND soi.trajet=aff.trajet
    WHERE sco.millesime=%millesime%
