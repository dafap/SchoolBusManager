<?php
/**
 * Calcul de la réduction et de la grille tarifaire pour un R2
 *
 * Pour l'utilisation, penser à initialiser la date de demande du R2 avant de lancer les calculs
 * $this->db_manager->get('Sbm\GrilleTarifR2')->setDateDemandeR2('2020-03-21')->appliquerTarif($eleveId);
 *
 * Surcharge de GrilleTarifR1 pour aplliquer les règles du R2 basées sur le règlement d'Arlysère
 * et sur la réponse de Monsieur Stéphane PIQUIER, par mail le 12 mars 2020 :
 * Un enfant est en garde alternée
 * 1. Son inscription se fait dans les délais.
 *    Le responsable 1 est dans Arlysere, le responsable 2 est hors Arlysère.
 *    Le montant à payer est de 110 € pour le R1 et de 0 € pour le R2
 * 2. Son inscription se fait toujours dans les délais.
 *    Le responsable 1 est hors Arlysère, le responsable 2 est dans Arlysère.
 *    Le montant à payer est de 200 € pour le R1 et de 0 € pour le R2
 * 3. Son inscription est hors délais.
 *    Le responsable 1 est dans Arlysère, le responsable 2 est hors Arlysère.
 *    Le montant à payer est de 165 € pour le R1 et de 55 € pour le R2
 * 4. Son inscription est hors délais.
 *    Le responsable 1 est hors Arlysère, le responsable 2 est dans Arlysère.
 *    Le montant à payer est de 200 € pour le R1 et de 55 € pour le R2
 *
 *
 * @project sbm
 * @package SbmCommun/src/Arlysere/Tarification
 * @filesource GrilleTarifR2.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Arlysere\Tarification;


class GrilleTarifR2 extends GrilleTarifR1
{

    /**
     * Date sous forme de chaine au format Y-m-d (MySql)
     *
     * @var string
     */
    private $dateDemandeR2 = '';

    /**
     *
     * @param string $dateDemandeR2
     * @return \SbmCommun\Arlysere\Tarification\GrilleTarifR2
     */
    public function setDateDemandeR2(string $dateDemandeR2)
    {
        $this->dateDemandeR2 = $dateDemandeR2;
        return $this;
    }

    /**
     * Surcharge pour affecter la grille calculée au R2
     *
     * @param int $eleveId
     * @return \SbmCommun\Arlysere\Tarification\GrilleTarifR2
     */
    protected function calculeGrilleTarif(int $eleveId)
    {
        $this->readEleve($eleveId);
        if ($this->oScolarite) {
            $this->scolariteChange = $this->oScolarite->grilleTarifR2 != $this->grilleTarif;
            $this->oScolarite->grilleTarifR2 = $this->grilleTarif;
        }
        return $this;
    }

    /**
     * Surcharge pour affecter la réduction calculée au R2
     *
     * @param int $eleveId
     * @return \SbmCommun\Arlysere\Tarification\GrilleTarifR2
     */
    protected function calculeReduction(int $eleveId)
    {
        $reduction = $this->periodeReduction($eleveId) ||
            $this->estPremiereInscription($eleveId) || $this->derogationObtenue($eleveId);
        if ($this->oScolarite) {
            $this->scolariteChange = $this->oScolarite->reductionR2 != $reduction;
            $this->oScolarite->reductionR2 = $reduction;
        }
        return $this;
    }

    /**
     * Indique si le R2 a demandé dans les délais
     *
     * @param int $eleveId
     * @return bool
     */
    protected function periodeReduction(int $eleveId): bool
    {
        $this->readEleve($eleveId);
        if ($this->oScolarite) {
            $dateInscription = \DateTime::createFromFormat('Y-m-d', $this->dateDemandeR2);
            return $dateInscription <= $this->etatDuSite['echeance'];
        } else {
            return false;
        }
    }

    protected function reglesGrille()
    {
        $this->grilleTarif = self::CARTE_R2;
    }
}