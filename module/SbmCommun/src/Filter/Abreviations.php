<?php
/**
 * Gestion des abréviations dans une ligne d'adresse
 *
 *
 * @project sbm
 * @package SbmCommun/Filter
 * @filesource Abreviations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 oct 2018
 * @version 2019-2.5.0
 */
namespace SbmCommun\Filter;

use Zend\Filter\AbstractUnicode;
use Zend\Filter\FilterInterface;

class Abreviations extends AbstractUnicode implements FilterInterface
{

    /**
     * Le filtre se déclanchera si la chaine à filtrer est de longueur supérieure au seuil.
     *
     * @var array
     */
    protected $options = [
        'encoding' => null,
        'seuil' => 38
    ];

    /**
     * Liste des mots et des abréviations correspondantes
     * Syntaxe :
     * Le mot est inscrit en minuscule et est accentué.
     * Lorsqu'il y a une variante, il est suivi d'une parenthèse qui contient les parties
     * changeantes.
     * Pour un pluriel comme allée, allées, la racine commune (avant la parenthèse) est allée et la
     * variante est (,s) qui veut dire
     * - ne rajoute rien ou rajoute un s
     * Pour un pluriel comme hôpital, hôpitaux, la racine commune (avant la parenthèse) est hôpit
     * et les variantes sont (al,aux) qui veut dire
     * - rajoute al ou rajoute aux
     * Il peut y avoir plus de 2 variantes. Il suffit de les séparer par une virgule dans la
     * parenthèse.
     *
     * Pour les mots composés, il peut y avoir 2 blocs de variantes - exemple : 'chemin(,s)
     * vicin(al,aux)'. Dans ce cas, il faut respecter
     * l'ordre des variantes sur les 2 mots : 'chemin vicinal' ou 'chemins vicinaux'.
     *
     * @var array
     */
    private $references = [
        'abbaye' => 'ABE',
        'adjudant' => 'ADJ',
        'aérodrome' => 'AER',
        'aérogare' => 'AERG',
        'aéronotique' => 'AERON',
        'aéroport' => 'AERP',
        'agence' => 'AGCE',
        'agglomération' => 'AGL',
        'agricole' => 'AGRIC',
        'allée(,s)' => 'ALL',
        'amiral' => 'AM',
        'ancien' => 'ANC',
        'ancien chemin' => 'ACH',
        'ancienne route(,s)' => 'ATR',
        'arcade(,s)' => 'ARC',
        'appartement' => 'APP',
        'armement' => 'ARMT',
        'arrondissement' => 'ARR',
        'aspirant' => 'ASP',
        'association' => 'ASSOC',
        'assurance' => 'ASSUR',
        'atelier' => 'AT',
        'autoroute' => 'AUT',
        'avenue' => 'AV',
        'baraquement' => 'BRQ',
        'barrière' => 'BRE',
        'bas(,se,ses)' => 'BAS',
        'bas chemin' => 'BCH',
        'bassin' => 'BSN',
        'bastide' => 'BSTD',
        'baston' => 'BAST',
        'bataillon(,s)' => 'BTN',
        'bâtiment(,s)' => 'BAT',
        'beguinage(,s)' => 'BEGI',
        'berge(,s)' => 'BER',
        'bis' => 'B',
        'boite postale' => 'BP',
        'boucle' => 'BCLE',
        'boulevard' => 'BD',
        'bourg' => 'BRG',
        'butte' => 'BUT',
        'campagne' => 'CGNE',
        'camping' => 'CPG',
        'capitaine' => 'CNE',
        'cardinal' => 'CDL',
        'carré' => 'CARR',
        'carreau' => 'CAU',
        'carrefour' => 'CAR',
        'carrière(,s)' => 'CARE',
        'castel' => 'CST',
        'cavée' => 'CAV',
        'centr(e,al)' => 'CTRE',
        'chalet' => 'CHL',
        'chanoine' => 'CHN',
        'chapelle' => 'CHP',
        'charmille' => 'CHI',
        'château' => 'CHT',
        'chaussée(,s)' => 'CHS',
        'chemin(,s)' => 'CHE',
        'chemin(,s) vicin(al,aux)' => 'CHV',
        'cheminement(,s)' => 'CHEM',
        'citadelle' => 'CTD',
        'cloitre' => 'CLOI',
        'collège' => 'COLL',
        'colline(,s)' => 'COLI',
        'colonel' => 'CEL',
        'combattants' => 'COMB',
        'commandant' => 'CDT',
        'commercial' => 'CIAL',
        'commission' => 'COMM',
        'commissaire' => 'CRE',
        'commissariat' => 'CIAT',
        'commun(e,al,aux)' => 'COM',
        'compagnie' => 'CIE',
        'compagnons' => 'COMPAG',
        'conseiller' => 'CONS',
        'contour' => 'CTR',
        'coopérative' => 'COOP',
        'corniche(,s)' => 'COR',
        'coteau' => 'COTE',
        'cottage(,s)' => 'COTT',
        'couloir' => 'CLR',
        'cours' => 'CRS',
        'croix' => 'CRX',
        'darse' => 'DARS',
        'degré(,s)' => 'DEG',
        'département(,al,aux)' => 'DEP',
        'descente(,s)' => 'DSC',
        'digue(,s)' => 'DIG',
        'direct(eur,ion)' => 'DIR',
        'division' => 'DIV',
        'douanier' => 'DOUA',
        'docteur' => 'DR',
        'domaine(,s)' => 'DOM',
        'ecart' => 'ECA',
        'ecluse(,s)' => 'ECL',
        'ecole(,s)' => 'EC',
        'economique' => 'ECON',
        'eglise' => 'EGL',
        'enceinte' => 'EN',
        'enclave' => 'ENV',
        'enseignement' => 'ENST',
        'entrée(,s)' => 'ENT',
        'entreprise' => 'ENTR',
        'emplacement' => 'EMP',
        'epou(x,se)' => 'EP',
        'esplanade(,s)' => 'ESP',
        'etablissement(,s)' => 'ETS',
        'etage' => 'ETG',
        'etat-major' => 'EM',
        'europ(e,éen,éenne,éens,éennes)' => 'EUR',
        'evêque' => 'EVQ',
        'faculté' => 'FAC',
        'faubourg' => 'FG',
        'ferme(,s)' => 'FRM',
        'fontaine' => 'FON',
        'for(êt,estier)' => 'FOR',
        'forum' => 'FORM',
        'fosse(,s)' => 'FOS',
        'foyer' => 'FOYR',
        'français(,e,es)' => 'FR',
        'galerie(,s)' => 'GAL',
        'garenne' => 'GARN',
        'général' => 'GEN',
        'gendarmerie' => 'GEND',
        'gouverneur' => 'GOU',
        'gouvernement(al,ale,ales,aux)' => 'GOUV',
        'grand(,e,es,s)' => 'GD',
        'grand boulevard' => 'GBD',
        'grand(,s) ensemble(,s)' => 'GDEN',
        'grande(,s) rue(,s)',
        'grille' => 'GRI',
        'grimpette' => 'GRIM',
        'groupe(s)' => 'GPE',
        'groupement' => 'GPT',
        'halage' => 'HLG',
        'halle(,s)' => 'HLE',
        'hameau(,x)' => 'HAM',
        'haut' => 'HT',
        'haute' => 'HTE',
        'hautes' => 'HTES',
        'hauts' => 'HTS',
        'haut(,s) chemin(,s)' => 'HCH',
        'hippodrome' => 'HIP',
        'hôpit(al,aux)' => 'HOP',
        'hospi(ce,talier)' => 'HOSP',
        'hôtel' => 'HOT',
        'immeuble(,s)' => 'IMM',
        'impasse(,s)' => 'IMP',
        'ingénieur' => 'ING',
        'infanterie' => 'INF',
        'inspecteur' => 'INSP',
        'institut' => 'INST',
        'internation(al,ale,aux,ales)' => 'INT',
        'jardin(,s)' => 'JARD',
        'jetée(,s)' => 'JTE',
        'laboratoire' => 'LABO',
        'levée' => 'LEVE',
        'lieu-dit' => 'LD',
        'lieutenant' => 'LT',
        'lieutenant de vaisseau' => 'LTDV',
        'lycée' => 'LYC',
        'lotissement(,s)' => 'LOT',
        'madame' => 'MME',
        'mademoiselle' => 'MLLE',
        'magasin' => 'MAG',
        'mairie' => 'MRIE',
        'maison' => 'MAIS',
        'maison forestière' => 'MF',
        'maître' => 'ME',
        'manoir' => 'MAN',
        'marche(,s)' => 'MAR',
        'maréchal' => 'MAL',
        'maritime' => 'MARIT',
        'méd(ecin,ical)' => 'MED',
        'mesdames' => 'MMES',
        'mesdemoiselles' => 'MLLES',
        'messieurs' => 'MM',
        'métro' => 'MET',
        'miliatire' => 'MIL',
        'ministère' => 'MIN',
        'monsieur' => 'M',
        'monseigneur' => 'MGR',
        'montée(,s)' => 'MTE',
        'moulin(,s)' => 'MLN',
        'municip(al,aux,ale,ales)' => 'MUN',
        'musée' => 'MUS',
        'mutuel' => 'MUT',
        'national' => 'NAT',
        'nouve(au,lle)' => 'NOUV',
        'nouvelle route' => 'NTE',
        'observatoire' => 'OBS',
        'palais' => 'PAL',
        'parking' => 'PKG',
        'parvis' => 'PRV',
        'passage' => 'PAS',
        'passage à niveau' => 'PN',
        'passe(,s)' => 'PASS',
        'passerelle(,s)' => 'PLE',
        'patio' => 'PAT',
        'pavillon(,s)' => 'PAV',
        'périphérique' => 'PERI',
        'péristyle' => 'PSTY',
        'petit(,e)' => 'PT',
        'petite(,s) allée(,s)' => 'PTA',
        'petit chemin' => 'PCH',
        'petite avenue' => 'PAE',
        'petite impasse' => 'PIM',
        'petite route' => 'PRT',
        'petite rue' => 'PTR',
        'place' => 'PL',
        'placis' => 'PLCI',
        'plage(,s)' => 'PLAG',
        'plaine' => 'PLN',
        'plateau(,x)' => 'PLT',
        'pointe' => 'PNT',
        'porche' => 'PCH',
        'porte' => 'PTE',
        'portique(,s)' => 'PORQ',
        'poterne' => 'POT',
        'pourtour' => 'POUR',
        'pesqu\'île' => 'PRQ',
        'préfet(ecture)' => 'PREF',
        'président' => 'PDT',
        'professeur' => 'PR',
        'professionnel(,le,les,s)' => 'PROF',
        'promenade' => 'PROM',
        'quai' => 'QU',
        'quartier' => 'QUA',
        'quater' => 'Q',
        'quinquies' => 'C',
        'raccourci' => 'RAC',
        'raidillon' => 'RAID',
        'rampe' => 'RPE',
        'recteur' => 'RECT',
        'régiment' => 'RGT',
        'région(,al,ale,ales,aux)' => 'REG',
        'rempart' => 'REM',
        'république' => 'REP',
        'résidence(,s)' => 'RES',
        'restaurant' => 'REST',
        'roc(,ade,ades)' => 'ROC',
        'rond point' => 'RPT',
        'roquet' => 'ROQT',
        'rotonde' => 'RTD',
        'route(,s)' => 'RTE',
        'rue(,s)' => 'R',
        'ruelle(,s)' => 'RLE',
        'saint' => 'ST',
        'sainte' => 'STE',
        'saintes' => 'STES',
        'saints' => 'STS',
        'sente(,ier,iers,s)' => 'SEN',
        'sergent' => 'SGT',
        'service' => 'SCE',
        'société' => 'SOC',
        'sous-préfe(t,cture)' => 'SPREF',
        'sous couvert' => 'SC',
        'square' => 'SQ',
        'stade' => 'STDE',
        'station' => 'STA',
        'techni(cien,que)' => 'TECH',
        'ter' => 'T',
        'terre plein' => 'TPL',
        'terrain' => 'TRN',
        'terrasse(,s)' => 'TSSE',
        'tertre(,s)' => 'TRT',
        'traverse' => 'TRA',
        'université' => 'UNIV',
        'universitaire' => 'UNVT',
        'val(,lée,lon)' => 'VAL',
        'vélodrome' => 'VELO',
        'venelle(,s)' => 'VEN',
        'veuve' => 'VVE',
        'vieille' => 'VIEL',
        'vieille route' => 'VTE',
        'vieux' => 'VX',
        'vieux chemin' => 'VCHE',
        'villa(,s)' => 'VLA',
        'village(,s)' => 'VGE',
        'ville(,s)' => 'VIL',
        'voie(,s)' => 'VOI',
        'zone artizanale' => 'ZA',
        'zone industrielle' => 'ZI'
    ];

    private $abreviations = [];

    /**
     * Constructor
     *
     * @param string|array|\Traversable $encodingOrOptions
     *            OPTIONAL
     */
    public function __construct($encodingOrOptions = null)
    {
        $matches = null;
        if ($encodingOrOptions !== null) {
            if (! static::isOptions($encodingOrOptions)) {
                $this->setEncoding($encodingOrOptions);
            } else {
                $this->setOptions($encodingOrOptions);
            }
        }
        $sa = new SansAccent();
        $encoding = $this->options['encoding'];
        foreach ($this->references as $mots => $abrev) {
            $pattern = '[ ]*(.*)[\(](.*)[\)]';
            $nparts = mb_substr_count($mots, '(', $encoding);
            if ($nparts == 0) {
                $this->abreviations[$mots] = $abrev;
                $this->abreviations[$sa->filter($mots)] = $abrev;
            } elseif ($nparts == 1) {
                preg_match('#' . $pattern . '#', $mots, $matches);
                foreach (explode(',', $matches[2]) as $suffixe) {
                    $idx = $matches[1] . $suffixe;
                    $this->abreviations[$idx] = $abrev;
                    $this->abreviations[$sa->filter($idx)] = $abrev;
                }
            } else { // mot composé de 2 parties seulement
                preg_match('#' . $pattern . $pattern . '#', $mots, $matches);
                $suffixes1 = explode(',', $matches[2]);
                $suffixes2 = explode(',', $matches[4]);
                if (count($suffixes1) != count($suffixes2)) {
                    throw new Exception\LogicException(
                        'Erreur de référence dans le fichier ' . __FILE__ .
                        '. Les deux parties du mot composé doivent avoir le même nombre de variantes.');
                }
                for ($i = 0; $i < count($suffixes1); $i ++) {
                    $idx = $matches[1] . $suffixes1[$i] . ' ' . $matches[3] .
                        $suffixes2[$i];
                    $this->abreviations[$idx] = $abrev;
                    $this->abreviations[$sa->filter($idx)] = $abrev;
                }
            }
        }
        if (! uksort($this->abreviations,
            function ($k1, $k2) use ($encoding) {
                $l1 = mb_strlen($k1, $encoding);
                $l2 = mb_strlen($k2, $encoding);
                if ($l1 < $l2) {
                    return 1; // les plus courts après les plus longs
                } elseif ($l1 > $l2) {
                    return - 1; // les plus long avant les plus courts
                } else {
                    return strcasecmp($k1, $k2); // ordre alphabétique pour des mots de même
                                                 // longueur
                }
            })) {
            throw new Exception\RuntimeException(
                __CLASS__ . '. Impossible de trier le tableau des abréviations.');
        }
    }

    public function filter($val)
    {
        if (is_string($val)) {
            if (mb_strlen($val, $this->options['encoding']) > $this->options['seuil']) {
                $val = mb_strtolower($val, $this->options['encoding']);
                foreach ($this->abreviations as $idx => $abr) {
                    $val = implode($abr, mb_split($idx, $val));
                    if (mb_strlen($val, $this->options['encoding']) <=
                        $this->options['seuil'])
                        return $val;
                }
            }
        }
        return $val;
    }
}