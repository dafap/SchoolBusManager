<?php
/**
 * AbonnementsFratrie correspond à un responsable
 *
 * La création du service initialise les tarifs en interrogeant la table des tarifs.
 * Cette classe reçoit une liste des enfants d'un responsable avec leur grille tarifaire
 * (eleveId, grille) par la méthode setEleves.
 * La méthode addEleve() permet d'ajouter un élève à la liste.
 * La méthode resetEleves() vide la liste des élèves.
 * Elle calcule les montants des abonnements à appliquer à chaque enfant par la méthode calcule().
 * Elle renvoie le montant total à payer par la méthode total()
 * Elle renvoie le détail des sommes à payer par la méthode detail()
 *
 * La modification de la liste des élèves vide la table abonnements
 * La méthode calcule() est automatiquement lancée par les méthodes total() et detail() si nécessaire.
 *
 *
 * @project sbm
 * @package SbmCommun/src/Arlysere/Taririfation/Facture;
 * @filesource AbonnementsFratrie.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Arlysere\Tarification\Facture;

use SbmCommun\Model\Db\Service\DbManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;

class AbonnementsFratrie implements FactoryInterface
{

    /**
     *
     * @var DbManager
     */
    private $dbManager;

    /**
     * Grille tarifaire. C'est un tableau de tableaux. Les clés de niveau 1 sont les
     * numéros de grille. Les clés de niveau 2 sont les seuils. Exemple : [ 1 =>
     * [2=>110,1=>110,3=>55, 4=>0], 2 => [1=>200,2=>200,3=>100,4=>0], 3 => [2=>55, 3=>28,
     * 4=>0], 4 => [1=>0] ]. Peu importe l'ordre des seuils dans les grilles et peu
     * importe l'ordre des grilles.
     *
     * @var array
     */
    private $tarifs;

    /**
     * Ce tableau indexé présente pour chaque enregistrement un tableau associatif
     * possédant au moins les clés 'eleveId' et 'grille'
     *
     * @var array
     */
    private $eleves;

    /**
     * Tableau d'Abonnement. Il est initialisé par la méthode calcul() et on en tire le
     * contenu par la méthode detail() et le montant total des abonnements par la méthode
     * total()
     *
     * @var array
     */
    private $abonnements;

    public function __construct(DbManager $dbManager)
    {
        $this->dbManager = $dbManager;
        $this->eleves = [];
        $this->abonnements = [];
    }


    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (!($serviceLocator instanceof DbManager)) {
            $msg = 'Le serviceLocator reçu doit être un DbManager';
            throw new \SbmCommun\Arlysere\Exception\InvalidArgumentException($msg);
        }
        $tTarifs = $serviceLocator->get('Sbm\Db\Table\Tarifs');
        $resultset = $tTarifs->fetchAll('duplicata = 0');
        $this->tarifs = [];
        foreach ($resultset as $row) {
            $this->tarifs[$row->grille][$row->seuil] = $row->montant;
        }
    }

    /**
     * Vide le tableau des élèves
     *
     * @return \SbmCommun\Arlysere\Tarification\Facture\AbonnementsFratrie
     */
    public function resetEleves()
    {
        $this->eleves = [];
        $this->abonnements = [];
        return $this;
    }

    /**
     * Initialise le tableau des élèves. Chaque élève du tableau se présente sous la forme
     * d'un tableau associatif ayant au moins les clés 'eleveId' et 'grille'.
     *
     * @param array $eleves
     * @throws \SbmCommun\Arlysere\Exception\InvalidArgumentException
     * @return \SbmCommun\Arlysere\Tarification\Facture\AbonnementsFratrie
     */
    public function setEleves(array $eleves)
    {
        foreach ($eleves as $row) {
            if (! (is_array($row) && array_key_exists('eleveId', $row) &&
                array_key_exists('grille', $row))) {
                throw new \SbmCommun\Arlysere\Exception\InvalidArgumentException(
                    'Argument incorrect');
            }
        }
        $this->eleves = $eleves;
        $this->abonnements = [];
        return $this;
    }

    /**
     * Ajoute un élève dans le tableau des élèves. L'élève est passé sour la forme d'un
     * tableau associatif ayant au moins les clés 'eleveId' et 'grille'.
     *
     * @param array $eleve
     * @throws \SbmCommun\Arlysere\Exception\InvalidArgumentException
     * @return \SbmCommun\Arlysere\Tarification\Facture\AbonnementsFratrie
     */
    public function addEleve(array $eleve)
    {
        if (! (array_key_exists('eleveId', $eleve) && array_key_exists('grille', $eleve))) {
            throw new \SbmCommun\Arlysere\Exception\InvalidArgumentException(
                'Argument incorrect');
        }
        $this->eleves[] = $eleve;
        $this->abonnements = [];
        return $this;
    }

    /**
     * Calcule le tarif à appliquer pour chaque élève.
     *
     * @return \SbmCommun\Arlysere\Tarification\Facture\AbonnementsFratrie
     */
    public function calcule()
    {
        $array = [];
        foreach ($this->eleves as $row) {
            $array[] = new Abonnement($row['grille'], $this->tarifs[$row['grille']],
                $row['eleveId']);
        }
        sort($array);
        for ($i = 0; $i < count($array); $i ++) {
            $array[$i]->appliqueMontant($i + 1);
        }
        $this->abonnements = $array;
        return $this;
    }

    /**
     *
     * @return number
     */
    public function total()
    {
        $total = 0;
        if (! $this->abonnements) {
            $this->calcule();
        }
        foreach ($this->abonnements as $row) {
            $total += $row;
        }
        return $total;
    }

    /**
     *
     * @return array|\SbmCommun\Arlysere\Tarification\Facture\Abonnement[]
     */
    public function detail()
    {
        if (! $this->abonnements) {
            $this->calcule();
        }
        return $this->abonnements;
    }
}
