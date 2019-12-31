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
 * @date 17 déc. 2019
 * @version 2019-2.5.4
 */
namespace SbmCommun\Filter;

use Zend\Filter\AbstractUnicode;
use Zend\Filter\FilterInterface;

class Abreviations extends AbstractUnicode implements FilterInterface
{

    /**
     * Le filtre se déclanchera si la chaine à filtrer est de longueur supérieure au
     * seuil.
     *
     * @var array
     */
    protected $options = [
        'encoding' => null,
        'seuil' => 38
    ];

    /**
     * Liste des mots et des abréviations correspondantes Syntaxe : Le mot est inscrit en
     * minuscule et est accentué. Lorsqu'il y a une variante, il est suivi d'une
     * parenthèse qui contient les parties changeantes. Pour un pluriel comme allée,
     * allées, la racine commune (avant la parenthèse) est allée et la variante est (,s)
     * qui veut dire - ne rajoute rien ou rajoute un s Pour un pluriel comme hôpital,
     * hôpitaux, la racine commune (avant la parenthèse) est hôpit et les variantes sont
     * (al,aux) qui veut dire - rajoute al ou rajoute aux Il peut y avoir plus de 2
     * variantes. Il suffit de les séparer par une virgule dans la parenthèse. Pour les
     * mots composés, il peut y avoir 2 blocs de variantes - exemple : 'chemin(,s)
     * vicin(al,aux)'. Dans ce cas, il faut respecter l'ordre des variantes sur les 2 mots
     * : 'chemin vicinal' ou 'chemins vicinaux'.
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
        'intendant' => 'ITD',
        'internation(al,ale,aux,ales)' => 'INT',
        'jardin(,s)' => 'JARD',
        'jetée(,s)' => 'JTE',
        'laboratoire' => 'LABO',
        'levée' => 'LEVE',
        'lieu-dit' => 'LDT',
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
        'quartier' => 'QRT',
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

    private $inverse = [];

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
                $this->inverse[$abrev] = $mots;
            } elseif ($nparts == 1) {
                preg_match('#' . $pattern . '#', $mots, $matches);
                foreach (explode(',', $matches[2]) as $suffixe) {
                    $idx = $matches[1] . $suffixe;
                    $this->abreviations[$idx] = $abrev;
                    $this->abreviations[$sa->filter($idx)] = $abrev;
                    if (! array_key_exists($abrev, $this->inverse)) {
                        $this->inverse[$abrev] = $idx;
                    }
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
                if (! array_key_exists($abrev, $this->inverse)) {
                    $this->inverse[$abrev] = $matches[1] . $suffixes1[0] . ' ' .
                        $matches[3] . $suffixes2[0];
                    ;
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
                    return strcasecmp($k1, $k2); // ordre alphabétique pour des mots de
                                                 // même
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

    /**
     * 2 étapes, l'une pour corriger les fautes d'orthographe et les abréviations l'autre
     * pour corriger les majuscules avec Saint ou Sainte
     *
     * @param string $val
     *            valeur à adapter si nécessaire
     * @return string valeur rectifiée
     */
    public function unfilter($val)
    {
        if (is_string($val)) {
            $array = explode(' ', $val);
            foreach ($array as &$mot) {
                $mot = $this->correction($mot);
                $amot = explode('\'', $mot);
                if (count($amot) > 1 && ! $this->isMaj($mot)) {
                    if (strcmp($amot[0], 'D') == 0) {
                        $amot[0] = 'd';
                        $amot[1] = $this->toUlower($amot[1]);
                    } elseif (strcmp($amot[0], 'L') == 0) {
                        $amot[0] = 'l';
                        $amot[1] = $this->toUlower($amot[1]);
                    }
                    $mot = implode('\'', $amot);
                }
            }
            $val = str_replace('\' ', '\'', implode(' ', $array));
            // à faire ici afin que soient traités les remplacements de St et Ste dans
            // la méthode correction()
            if (stripos($val, 'saint') !== false) {
                $tmp = preg_replace('/(.*)?(sainte?)[ -](.*)/i', '$1$2-$3', $val);
                if (! empty($tmp)) {
                    $val = $tmp;
                }
                $array = explode(' ', $val);
                foreach ($array as &$mot) {
                    $mot = $this->saint($mot);
                }
                $val = str_replace('\' ', '\'', implode(' ', $array));
            }
        }
        return $this->toMaj($val);
    }

    public function correction($mot)
    {
        $uppercase = mb_strtoupper($mot, $this->options['encoding']);
        $lowercase = mb_strtolower($uppercase, $this->options['encoding']);
        if (array_key_exists($uppercase, $this->inverse)) {
            $lowercase2 = mb_strtolower($this->inverse[$uppercase],
                $this->options['encoding']);
        } else {
            $lowercase2 = $lowercase;
        }
        if ($this->isMin($mot)) {
            return $this->toMin($this->orthographe($lowercase2));
        } elseif ($this->isMaj($mot)) {
            return $this->toMaj($this->orthographe($lowercase2));
        } else {
            $tmp = $this->toUlower($this->orthographe($lowercase2));
            $tmp = str_replace([
                'Jean-jacques',
                'Beau-soleil'
            ], [
                'Jean-Jacques',
                'Beau-Soleil'
            ], $tmp);
            return $tmp;
        }
    }

    public function orthographe($mot)
    {
        $dic = [
            'residence' => 'résidence',
            'residences' => 'résidences',
            'allee' => 'allée',
            'serenite' => 'sérénité',
            'carratieres' => 'carratières',
            'cite' => 'cité',
            'imp.' => 'impasse',
            'cap d ase' => 'cap d\'ase',
            'rozieres' => 'rozières',
            'general' => 'général',
            'mal' => 'maréchal',
            'marechal' => 'maréchal',
            'edouard' => 'édouard',
            'elise' => 'élise',
            'emile' => 'émile',
            'etienne' => 'étienne',
            'leopold' => 'léopold',
            'leomard' => 'léonard',
            'aime' => 'aimé',
            'andre' => 'andré',
            'cesaire' => 'césaire',
            'creve' => 'crève',
            'abbe' => 'abbé',
            'cle' => 'clé',
            'etroite' => 'étroite',
            'liberte' => 'liberté',
            'egalite' => 'égalité',
            'fraternite' => 'fraternité',
            'laitiere' => 'laitière',
            'negre' => 'nègre',
            'republique' => 'république',
            'cantiniere' => 'cantinière',
            'resistance' => 'résistance',
            'comedie' => 'comédie',
            'eglise' => 'église',
            'pomarede' => 'pomarède',
            'dandan' => 'd\'andan',
            'poumiere(la)' => 'poumiere (la)',
            'aumieres' => 'aumières',
            'mere' => 'mère',
            'pere' => 'père',
            'l\'egalité' => 'l\'égalité',
            'l\'hot' => 'l\'hôtel',
            'thalweg' => 'talweg',
            'cres' => 'crès',
            'lescalopier' => 'l\'escalopier',
            'constant' => 'constans',
            'moliere' => 'molière',
            'megisserie' => 'mégisserie',
            'aimê' => 'aimé',
            'd' => 'd\'',
            'l' => 'l\''
        ];
        if (array_key_exists($mot, $dic)) {
            return $dic[$mot];
        }
        return $mot;
    }

    private function saint($mot)
    {
        if (stripos($mot, 'saint') !== false) {
            if (! $this->isMaj($mot) && ! $this->isMin($mot)) {
                $array = explode('-', $mot);
                foreach ($array as &$value) {
                    $value = $this->toUlower($value);
                }
                return implode('-', $array);
            }
        }
        return $mot;
    }

    private function isMaj($mot)
    {
        return strcmp($mot, $this->toMaj($mot)) == 0;
    }

    private function isMin($mot)
    {
        return strcmp($mot, $this->toMin($mot)) == 0;
    }

    private function toMaj($mot)
    {
        return mb_strtoupper($mot, $this->options['encoding']);
    }

    private function toMin($mot)
    {
        return mb_strtolower($mot, $this->options['encoding']);
    }

    private function toUlower($mot)
    {
        return $this->toMaj(mb_substr($mot, 0, 1, $this->options['encoding'])) .
            $this->toMin(mb_substr($mot, 1, null, $this->options['encoding']));
    }
}