<?php
/**
 * Tableau simple
 *
 * Chaque document est décrit par les tables 'documents', 'doctables' et 'doccolumns'.
 * Les données sont passées par la procédure appelante ou sont issues d'une requête
 * sur le 'recordSource' en appliquant les critères de sélection.
 *
 * Le tableau obtenu ne peut pas fusionner de cellules. Chaque cellule ne peut contenir
 * qu'une chaine de caractères sur une seule ligne d'écriture. Les \n ou <br> ne sont
 * pas gérés.
 *
 * @project sbm
 * @package SbmPdf/src/Model/Document/Template
 * @filesource TableSimple.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 févr. 2021
 * @version 2021-2.6.1
 */
namespace SbmPdf\Model\Document\Template;

use SbmBase\Model\DateLib;
use SbmBase\Model\StdLib;
use SbmPdf\Model\Element\ProcessFeatures;
use SbmPdf\Model\Document;
use SbmPdf\Model\Exception;
use Zend\Stdlib\Parameters;
use SbmPdf\Model\Db\Sql\Select;

class TableSimple extends Document\AbstractDocument
{
    use Document\DocumentTrait, \SbmPdf\Model\Tcpdf\TcpdfTrait, \SbmCommun\Model\Traits\DebugTrait;

    const PDFMANAGER_ID = 'tableSimple';

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    /**
     * Objet tenant à jour les pointeurs de début et courant pour les calculs par page ou
     * par
     * groupe et les résultats des calculs (par page, par groupe ou tout)
     *
     * @var ProcessFeatures
     */
    private $oProcess;

    public static function description()
    {
        return new Parameters(
            [
                'libelle' => 'Tableau simple',
                'description' => <<<EOT
                Ce modèle produit un tableau où :<ul>
                <li>chaque cellule ne peut contenir qu'une seule ligne de texte.</li>
                <li>les retours à la ligne dans les données ne sont pas interprétés.</li>
                <li>ce modèle ne permet pas la fusion de cellules.</li></ul>
                EOT
            ]);
    }

    /**
     * Initialise la méthode majIndexAddPage (closure).
     * Initialise les variables privées de cette classe.
     * Complète la propriété $config en ajoutant une section 'doctable'
     * L'accès de fait par :
     * $this->config->doctable->{propriété demandée}
     * A noter que la propriété 'columns' de 'doctable' provenant de la table 'doccolumns'
     * est un tableau
     *
     * {@inheritdoc}
     * @see \SbmPdf\Model\Document\AbstractDocument::init()
     */
    protected function init()
    {
        $this->debugInitLog(StdLib::findParentPath(__DIR__, 'data/logs'),
            'tablesimple.log');
        $this->db_manager = $this->pdf_manager->get('Sbm\DbManager');
        // met en place la méthode à appeler dans pdf->AddPage() pour mettre à jour les
        // pointeurs
        $this->oProcess = new ProcessFeatures();
        $this->majIndexAddPage = function () {
            $this->oProcess->newPage();
        };
        // complète la propriété 'config'
        try {
            $config_table = $this->pdf_manager->get('Sbm\DbManager')
                ->get('Sbm\Db\System\DocTables')
                ->getConfig($this->documentId);
        } catch (\Exception $e) {
            $config_table = require (__DIR__ . '/default/doctables.inc.php');
        }
        try {
            $config_table['columns'] = $this->pdf_manager->get('Sbm\DbManager')
                ->get('Sbm\Db\System\DocTables\Columns')
                ->getConfig($this->documentId);
        } catch (\Exception $e) {
            // pas d'en-tête, pas de pied, colonnes proportionnelles à la taille du
            // contenu
            $config_table['thead']['visible'] = $config_table['tfoot']['visible'] = false;
        }
        $this->setConfig('doctable', $config_table);
    }

    /**
     * On regarde si un paramètre 'data' est connu.
     * Sinon, on recherche les datas à partir de recordSource.
     */
    protected function getData(bool $force = false)
    {
        if ($force || empty($this->dada)) {
            if ($this->params->offsetExists('data')) {
                // les datas sont fournies par la méthode appelante
                $this->data = $this->params->get('data', false);
            } elseif ($this->params->offsetExists('recordSource')) {
                // la source de données est fournie par la méthode appelante
                $recordSource = $this->params->document->recordSource;
                if ($this->params->document->get('recordSourceType', false) == 'T') {
                    // la source est une table
                    $this->data = $this->getDataFromTable($recordSource, 'params');
                } else {
                    // la source est une requête SQL
                    $this->data = $this->getDataFromQuery($recordSource, 'params');
                }
            } else {
                $recordSource = $this->config->document->recordSource;
                if ($this->config->document->recordSourceType == 'T') {
                    // la source est une table
                    $this->data = $this->getDataFromTable($recordSource, 'config');
                } else {
                    // la source est une requête SQL
                    $this->data = $this->getDataFromQuery($recordSource, 'config');
                }
            }
        }
        return $this->data;
    }

    /**
     *
     * @param string $recordSource
     * @param string $origine
     * @return array (tableau à 2 dimensions)
     */
    private function getDataFromTable(string $recordSource, string $origine)
    {
        $this->validTable($recordSource, $origine);
        $table = $this->db_manager->get($recordSource);
        if (empty($this->config->doctable->columns)) {
            // ajoute une configuration des colonnes dans $this->config->doctable
            $this->addDoctableColumnsFromTable($table);
        }
        // modifie si nécessaire $this->config->doctable->columns[$i]['filter']
        $this->sanitizeColumnFilters();
        // obtient la classe à utiliser pour le calcul des effectifs d'élèves. Null sinon.
        $effectifClass = $this->getEffectifClassForTable($recordSource);
        // lit les données et ajuste les largeurs de colonnes
        $data = [];
        $doctable_columns = $this->config->doctable->columns;
        foreach ($table->fetchAll($this->getWhere(), $this->getOrderBy()) as $row) {
            $ligne = [];
            foreach ($doctable_columns as &$column) {
                $ligne[] = $value = $this->getValueFromTableRecord($row, $column,
                    $effectifClass);
                // adapte la largeur de la colonne si nécessaire
                $this->adaptColumnWidth($value, $column);
            }
            $data[] = $ligne;
        }
        $this->config->doctable->set('columns', $doctable_columns);
        return $data;
    }

    /**
     *
     * @param \SbmCommun\Model\Db\ObjectData\ObjectDataInterface $row
     * @param array $column
     * @param null|\SbmGestion\Model\Db\Service\EffectifInterface $effectifClass
     * @return string|number|null
     */
    private function getValueFromTableRecord($row, $column, $effectifClass)
    {
        try {
            // interprète la traduction éventuellement définie dans le filter
            $value = StdLib::translateData($row->{$column['tbody']}, $column['filter']);
            // 3 cas : photo, date ou autre
            switch ($column['nature']) {
                case 2:
                    // photo
                    if ($value) {
                        $value = '@' . stripslashes($value);
                    }
                    break;
                case 1:
                    // date (avec time si $column['format'] contient le caractère 'h')
                    if (! empty($column['format']) &&
                        stripos('h', $column['format']) !== false) {
                        $value = DateLib::formatDateTimeFromMysql($value);
                    } else {
                        $value = DateLib::formatDateFromMysql($value);
                    }
                    break;
                default:
                    // prise en compte de $column['format'] par printf()
                    if ($column['format']) {
                        $value = sprintf($column['format'], $value);
                    }
                    break;
            }
            return $value;
        } catch (\Exception $e) {
            // calcul de l'effectif : le nom de la méthode est entre les '%' dans
            // $column['tbody']
            if ($effectifClass instanceof \SbmGestion\Model\Db\Service\EffectifInterface) {
                $columntbody = trim($column['tbody'], '%');
                if (method_exists($effectifClass, $columntbody)) {
                    return $effectifClass->{$columntbody}(
                        $row->{$effectifClass->getIdColumn()});
                }
                // la méthode n'est pas implémentée (ou erreur de paramétrage du document)
                return '?';
            }
            // chaine vide par défaut
            return '';
        }
    }

    /**
     * Recherche s'il y a des colonnes d'effectifs élèves
     *
     * @param string $recordSource
     * @throws \SbmPdf\Model\Exception
     * @return null | \SbmGestion\Model\Db\Service\EffectifInterface;
     */
    private function getEffectifClassForTable(string $recordSource)
    {
        $columnEffectif = false;
        foreach ($this->config->doctable->columns as $column) {
            if (preg_match('/%(.*)%/', $column['tbody'])) {
                $columnEffectif = true;
            }
        }
        $effectifClass = null;
        // recherche et configuration de la classe de calcul des effectifs
        if ($columnEffectif) {
            // on peut passer effectifClassName par les paramètres d'appel du document
            // (depuis le controller) ou on construit ce nom à partir du nom
            // d'enregistrement de la table dans db_manager
            $effectifClassName = $this->params->get('effectifClassName',
                $this->getStringEffectifInterface($recordSource));
            if ($this->db_manager->has($effectifClassName)) {
                $effectifClass = $this->db_manager->get($effectifClassName);
                if (method_exists($effectifClass, 'setCaractereConditionnel')) {
                    if ($this->params->caractereConditionnel) {
                        $effectifClass->setCaractereConditionnel(
                            $this->params->caractereConditionnel)->init(
                            $this->params->sanspreinscrits ?: false);
                    } else {
                        // Mauvais appel dans le controller
                        if (getenv('APPLICATION_ENV') == 'development') {
                            throw new Exception(
                                "Le paramètre `caractereConditionnel` n'a pas été défini avant l'appel du document.");
                        }
                        $effectifClass = null;
                    }
                } else {
                    $effectifClass->init($this->params->sanspreinscrits ?: false);
                }
            }
        }
        return $effectifClass;
    }

    /**
     * Supprime les caractères blancs en début et en fin de ligne et transforme les
     * chaines représentant des tableaux en tableaux php.
     * Attention, passer par la méthode set de l'objet Parameters pour que les
     * modifications soient prises en compte dans $this->config->doctable->columns.
     */
    private function sanitizeColumnFilters()
    {
        $doctable_columns = $this->config->doctable->columns;
        foreach ($doctable_columns as &$column) {
            $column['filter'] = preg_replace([
                '/^\s+/',
                '/\s+$/'
            ], '', $column['filter']);
            if (! empty($column['filter']) && is_string($column['filter'])) {
                $column['filter'] = StdLib::getArrayFromString(
                    stripslashes($column['filter']));
            } else {
                $column['filter'] = [];
            }
        }
        $this->config->doctable->set('columns', $doctable_columns);
    }

    /**
     * Lorsque la configuration des colonnes est absente on la rajoute en mettant toutes
     * les colonnes de la table
     *
     * @param \SbmCommun\Model\Db\Service\Table\AbstractSbmTable $table
     */
    private function addDoctableColumnsFromTable(
        \SbmCommun\Model\Db\Service\Table\AbstractSbmTable $table)
    {
        $ordinal_position = 1;
        $table_columns = [];
        foreach ($table->getColumnsNames() as $column_name) {
            $column = require (__DIR__ . '/default/doccolumns.inc.php');
            $column['thead'] = $column['tbody'] = $column_name;
            $column['ordinal_position'] = $ordinal_position ++;
            $table_columns[] = $column;
        }
        $this->config->doctable->set('columns', $table_columns);
    }

    /**
     * Lance une exception si le paramètre $recordSource n'est pas une clé
     * d'enregistrement
     * d'une classe dans le db_manager
     *
     * @param string $recordSource
     * @param string $origine
     * @throws \SbmPdf\Model\Exception\OutOfBoundsException
     */
    private function validTable(string $recordSource, string $origine)
    {
        if (! $this->db_manager->has($recordSource)) {
            if (getenv('APPLICATION_ENV') == 'development') {
                $msg .= sprintf(
                    "%s\nLa clé `recordSource` est invalide pour ce document.\nClé reçue par %s : %s",
                    __METHOD__, $origine, $recordSource);
            } else {
                $msg = 'Mauvaise définition de la table.';
            }
            throw new Exception\OutOfBoundsException($msg);
        }
    }

    /**
     * Attention, passer par la méthode set de l'objet Parameters pour que les
     * modifications soient prises en compte dans $this->config->doctable->columns.
     *
     * @param string $sqlString
     * @throws \SbmPdf\Model\Exception
     * @return array (tableau à 2 dimensions)
     */
    private function getDataFromQuery(string $sqlString)
    {
        // modifie si nécessaire $this->config->doctable->columns[$i]['filter']
        $this->sanitizeColumnFilters();
        // structure contenant la liste des colonnes pour le sql Select, la classe à
        // utiliser pour le calcul des effectifs d'élèves, la liste des méthodes demandées
        $structColumns = $this->adaptColumnsForEffectif();
        // décodage de la requête sql
        $sqlString = $this->decodeSource($sqlString);
        $dbAdapter = $this->db_manager->getDbAdapter();
        try {
            $select = new Select($sqlString); // Attention ! SbmPdf\Model\Db\Sql
            $select->columns($structColumns->selectColumns)
                ->where($this->getWhere())
                ->order($this->getOrderBy());
            $sqlString = $select->getSqlString($dbAdapter->getPlatform());
            $rowset = $dbAdapter->query($sqlString,
                \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
            $data = [];
            if ($rowset->count()) {
                // si la description des colonnes est vide, on configure toutes
                // les colonnes de la source
                $this->addDoctableColumnsFromQuery($rowset->current()
                    ->getArrayCopy());
                // lit les données et ajuste les largeurs de colonnes
                $doctable_columns = $this->config->doctable->columns;
                foreach ($rowset as $row) {
                    $ligne = [];
                    $indexEffectifMethods = 0;
                    for ($key = 0; $key < count($doctable_columns); $key ++) {
                        $ligne[] = $value = $this->getValueFromQueryRecord($row,
                            $doctable_columns[$key], $structColumns, $indexEffectifMethods);
                        // adapte la largeur de la colonne si nécessaire
                        $this->adaptColumnWidth($value, $doctable_columns[$key]);
                    }
                    $data[] = $ligne;
                }
                $this->config->doctable->set('columns', $doctable_columns);
            }
            return $data;
        } catch (\Exception $e) {
            if (getenv('APPLICATION_ENV') == 'development') {
                $msg = sprintf('%s\n%s\n%s\n%s', __METHOD__, $e->getMessage(), $sqlString,
                    $e->getTraceAsString());
            } else {
                $msg = "Impossible d'exécuter la requête.\n" . $sqlString;
            }
            $errcode = $e->getCode();
            if (! empty($errcode) && ! is_numeric($errcode)) {
                $msg = sprintf('Erreur %s : %s', $errcode, $msg);
                $errcode = null;
            }
            throw new Exception($msg, $errcode, $e->getPrevious());
        }
    }

    /**
     * On distingue les colonnes d'effectif par le contenu de tbody qui est false
     *
     * @param \ArrayObject $row
     *            données à traiter, ligne résultat de la requête
     * @param array $column
     *            descripteur de la colonne à traiter dans config->doctable->columns
     * @param \stdClass $structColumns
     *            voir la méthode adaptColumnsForEffectif()
     * @param int $indexEffectifMethods
     *            index de progression du traitement des effectifs
     */
    private function getValueFromQueryRecord(\ArrayObject $row, array $column,
        \stdClass $structColumns, int &$indexEffectifMethods)
    {
        if ($column['tbody']) {
            // ce n'est pas une colonne d'effectif
            $value = StdLib::translateData($row[$column['tbody']], $column['filter']);
            switch ($column['nature']) {
                case 2: // photo
                    return $value ? '@' . stripslashes($value) : $value;
                    break;
                case 1: // date
                    if (! empty($column['format']) && stripos($column['format'], 'h')) {
                        return DateLib::formatDateTimeFromMysql($value);
                    } else {
                        return DateLib::formatDateFromMysql($value);
                    }
                    break;
                default: // autres nature de donnée
                    if ($column['format']) {
                        return sprintf($column['format'], $value);
                    } else {
                        return $value;
                    }
                    break;
            }
        } elseif (array_key_exists($indexEffectifMethods, $structColumns->effectifMethods)) {
            // c'est une colonne d'effectif
            $method = $structColumns->effectifMethods[$indexEffectifMethods ++];
            if ($structColumns->effectifClass instanceof \SbmGestion\Model\Db\Service\EffectifInterface &&
                method_exists($structColumns->effectifClass, $method)) {
                // la configuration est correcte
                return $structColumns->effectifClass->{$method}(
                    $row->{$structColumns->effectifClass->getIdColumn()});
            }
        }
        // autre cas
        return 0;
    }

    /**
     * Cette méthode va modifier $this->config->doctable->columns si des colonnes
     * d'effectifs sont demandées.
     * Elle renvoie un objet ayant 3 propriétés :<ul>
     * <li>selectColumns : pour indiquer les colonnes attendues dans le sql (sans
     * les effectifs)</li>
     * <li>effectifMethods : liste des demandes d'effectifs (contient les méthodes à
     * appeler dans effectifClass)</li>
     * <li>effectifClass : null ou de type
     * \SbmGestion\Model\Db\Service\EffectifInterface</li></ul>
     * Lorsqu'un $this->config->doctable->columns[$i]['tbody'] contient une demande
     * d'effectif, la méthode le modifie en y plaçant false.
     *
     * Attention, passer par la méthode set de l'objet Parameters pour que les
     * modifications soient prises en compte dans $this->config->doctable->columns.
     *
     *
     * @return object
     */
    private function adaptColumnsForEffectif()
    {
        $structColumns = (object) [
            'selectColumns' => [],
            'effectifMethods' => [],
            'effectifClass' => null
        ];
        $doctable_columns = $this->config->doctable->columns;
        foreach ($doctable_columns as &$column) {
            // on relève les demandes d'effectifs et on met false à leur place
            // dans $column['tbody'] pour ne pas rechercher la valeur dans la
            // requête.
            $matches = [];
            if (preg_match('/^%(.*)%$/', $column['tbody'], $matches)) {
                $structColumns->effectifMethods[] = $matches[1];
                $column['tbody'] = false;
            } else {
                $structColumns->selectColumns[] = $column['tbody'];
            }
        }
        $this->config->doctable->set('columns', $doctable_columns);
        // si on n'a pas indiqué de colonne on les prend toutes
        if (empty($structColumns->selectColumns)) {
            $structColumns->selectColumns[] = Select::SQL_STAR;
        }
        // recherche et configuration de la classe de calcul des effectifs
        if (! empty($structColumns->effectifMethods)) {
            $effectifClassName = $this->params->effectifClassName;
            if ($effectifClassName && $this->db_manager->has($effectifClassName)) {
                $structColumns->effectifClass = $this->db_manager->get($effectifClassName);
                if ($structColumns->effectifClass instanceof \SbmGestion\Model\Db\Service\EffectifInterface) {
                    // @todo : à utiliser directement $id = $effectifClass->getIdColumn();
                    if (method_exists($structColumns->effectifClass,
                        'setCaractereConditionnel')) {
                        if ($this->param->caractereConditionnel) {
                            $structColumns->effectifClass->setCaractereConditionnel(
                                $this->param->caractereConditionnel)->init(
                                $this->params->sanspreinscrits ?: false);
                        } else {
                            // Mauvais appel dans le controller
                            if (getenv('APPLICATION_ENV') == 'development') {
                                throw new Exception(
                                    "Le paramètre `caractereConditionnel` n'a pas été défini avant l'appel du document.");
                            }
                            $structColumns->effectifClass = null;
                        }
                    } else {
                        $structColumns->effectifClass->init(
                            $this->params->sanspreinscrits ?: false);
                    }
                } else {
                    $structColumns->effectifClass = null;
                }
            }
        }
        return $structColumns;
    }

    private function addDoctableColumnsFromQuery(array $record)
    {
        if (empty($this->config->doctable->columns)) {
            $table_columns = [];
            $ordinal_position = 1;
            foreach (array_keys($record) as $column_name) {
                $column = require (__DIR__ . '/default/doccolumns.inc.php');
                $column['thead'] = $column['tbody'] = $column_name;
                $column['ordinal_position'] = $ordinal_position ++;
                $table_columns[] = $column;
            }
            $this->config->doctable->set('columns', $table_columns);
        }
    }

    /**
     * Adapte la largeur de la colonne si nécessaire
     *
     * @param string $value
     * @param array $column
     * @param string $section
     *            'thead', 'tbody' ou 'tfoot correspondant à la config dans doctable
     */
    private function adaptColumnWidth(string $value, array &$column,
        string $section = 'tbody')
    {
        $value_width = $this->pdf->GetStringWidth($value,
            $this->config->document->get('data_font_family', PDF_FONT_NAME_DATA),
            $this->config->doctable->{$section}['font_style'] ?: '',
            $this->config->document->get('data_font_size', PDF_FONT_SIZE_DATA));
        $value_width += $this->pdf->getCellPaddings()['L'] +
            $this->pdf->getCellPaddings()['R'];
        if ($value_width > $column['width']) {
            $column['width'] = $value_width;
        }
    }

    /**
     * Adapte la largeur des colonnes si nécessaire pour que le tableau rentre en largeur
     * dans la page.
     *
     * Attention, passer par la méthode set de l'objet Parameters pour que les
     * modifications soient prises en compte dans $this->config->doctable->columns.
     *
     * @return number (largeur du tableau)
     */
    private function adaptTableWidthsToPage()
    {
        $pagedim = $this->pdf->getPageDimensions();
        $max_width = $pagedim['wk'] - $pagedim['lm'] - $pagedim['rm'];
        $sum_width = 0;
        foreach ($this->config->doctable->columns as $column) {
            $sum_width += $column['width'];
        }
        if (($table_width = StdLib::getParam('width', $this->config->doctable->tbody,
            'auto')) == 'auto') {
            $ratio = $sum_width > $max_width ? $max_width / $sum_width : 1;
        } else {
            $ratio = $max_width * $table_width / 100 / $sum_width;
        }

        // largeur des colonnes
        $doctable_columns = $this->config->doctable->columns;
        foreach ($doctable_columns as &$column) {
            if ($ratio < 1) {
                $column['thead_stretch'] = 1;
                $column['tbody_stretch'] = 1;
            }
            $column['width'] *= $ratio;
            unset($column);
        }
        $this->config->doctable->set('columns', $doctable_columns);

        return $sum_width * $ratio;
    }

    /**
     * Renvoie l'alignement en traduisant 'standard'
     *
     * @param mixed $value
     * @param string $align
     */
    private function getAlign($value, $align)
    {
        if (is_numeric($value)) {
            return $align == 'standard' ? 'R' : $align;
        } else {
            return $align == 'standard' ? 'L' : $align;
        }
    }

    protected function templateDocumentHeader()
    {
        $this->debugLog(__METHOD__);
    }

    /**
     * Attention, passer par la méthode set de l'objet Parameters pour que les
     * modifications soient prises en compte dans $this->config->doctable->columns.
     *
     * {@inheritdoc}
     * @see \SbmPdf\Model\Document\AbstractDocument::templateDocumentBody()
     */
    protected function templateDocumentBody()
    {
        $this->debugLog(__METHOD__);
        // prend en compte les entêtes de colonnes pour adapter leur largeur
        $doctable_columns = $this->config->doctable->columns;
        foreach ($doctable_columns as &$column) {
            $this->adaptColumnWidth($column['thead'], $column, 'thead');
        }
        $this->config->doctable->set('columns', $doctable_columns);
        // nécessaire pour ajuster les largeurs de colonnes en fonction des données
        $this->getData();
        // ajuste la largeur du tableau pour qu'il entre dans la page en largeur
        $table_width = $this->adaptTableWidthsToPage();
        // entête du tableau
        $this->templateThead();
        // corps du tableau
        $this->templateTbody();
        // pied du tableau
        $this->templateTfoot();
        $this->pdf->Cell($table_width, 0, '', 'T');
    }

    protected function templateDocumentFooter()
    {
        $this->debugLog(__METHOD__);
        ;
    }

    /**
     * Ajoute un entête de tableau dans le document pdf (en général la ligne d'entêtes de
     * colonnes)
     */
    protected function templateThead()
    {
        $this->debugLog(__METHOD__);
        if (! StdLib::getParam('visible', $this->config->doctable->thead))
            return;
        $this->configGraphicSectionTable('thead');
        foreach ($this->config->doctable->columns as $column) {
            $align = $this->getAlign($column['thead'], $column['thead_align']);
            $this->pdf->Cell($column['width'],
                $this->config->doctable->thead['row_height'],
                StdLib::formatData($column['thead'], $column['thead_precision'],
                    $column['thead_completion']),
                $this->config->doctable->thead['cell_border'], 0, $align, 1,
                $this->config->doctable->thead['cell_link'], $column['thead_stretch'],
                $this->config->doctable->thead['cell_ignore_min_height'],
                $this->config->doctable->thead['cell_calign'],
                $this->config->doctable->thead['cell_valign']);
        }
        $this->pdf->Ln();
    }

    protected function templateTbody()
    {
        $this->debugLog(__METHOD__);
        if (! StdLib::getParam('visible', $this->config->doctable->tbody))
            return;
        $this->configGraphicSectionTable('tbody');
        $this->oProcess->initGroup($this->config->doctable->columns);
        $fill = 0;
        foreach ($this->getData() as $row) {
            if ($this->oProcess->isNewGroup($row)) {
                $this->templateTfoot();
                $this->pdf->AddPage();
                $this->oProcess->newGroup($row);
                $this->templateThead();
                $this->configGraphicSectionTable('tbody');
            }
            for ($j = 0; $j < count($row); $j ++) {
                $align = $this->getAlign($row[$j],
                    $this->config->doctable->columns[$j]['tbody_align']);
                $this->pdf->Cell($this->config->doctable->columns[$j]['width'],
                    $this->config->doctable->tbody['row_height'],
                    StdLib::formatData($row[$j],
                        $this->config->doctable->columns[$j]['tbody_precision'],
                        $this->config->doctable->columns[$j]['tbody_completion']),
                    $this->config->doctable->tbody['cell_border'], 0, $align, $fill,
                    $this->config->doctable->tbody['cell_link'],
                    $this->config->doctable->columns[$j]['tbody_stretch'],
                    $this->config->doctable->tbody['cell_ignore_min_height'],
                    $this->config->doctable->tbody['cell_calign'],
                    $this->config->doctable->tbody['cell_valign']);
            }
            $this->oProcess->nextPointer();
            $this->pdf->Ln();
            $fill = ! $fill;
        }
    }

    /**
     * Ajoute un pied de tableau dans le document pdf
     */
    protected function templateTfoot()
    {
        $this->debugLog(__METHOD__);
        if (! StdLib::getParam('visible', $this->config->doctable->tfoot))
            return;
        $this->configGraphicSectionTable('tfoot');
        $index = 0;
        foreach ($this->config->doctable->columns as $column) {
            // @todo Vérifier Calculs pour le calcul sur la colonne $index
            $oCalculs = new \SbmPdf\Model\Calculs($this->getData(), ++ $index);
            $oCalculs->range($this->oProcess->getPointerPageBegin(),
                $this->oProcess->getPointerCurrent());
            $value = $oCalculs->getResultat($column['tfoot']);
            $align = $this->getAlign($column['tfoot'], $column['tfoot_align']);
            $this->pdf->Cell($column['width'],
                $this->config->doctable->tfoot['row_height'],
                StdLib::formatData($value, $column['tfoot_precision'],
                    $column['tfoot_completion']),
                $this->config->doctable->tfoot['cell_border'], 0, $align, 1,
                $this->config->doctable->tfoot['cell_link'], $column['tfoot_stretch'],
                $this->config->doctable->tfoot['cell_ignore_min_height'],
                $this->config->doctable->tfoot['cell_calign'],
                $this->config->doctable->tfoot['cell_valign']);
        }
        $this->pdf->Ln();
    }

    /**
     * Configuration graphique correspondant à une section de table
     *
     * @param string $section
     *            'thead', 'tbody' ou 'tfoot'
     */
    protected function configGraphicSectionTable($section)
    {
        $this->pdf->SetFont(
            $this->config->document->get('data_font_family', PDF_FONT_NAME_DATA),
            trim(StdLib::getParam('font_style', $this->config->doctable->{$section}, '')),
            $this->config->document->get('data_font_size', PDF_FONT_SIZE_DATA));
        $this->pdf->SetLineWidth(
            StdLib::getParam('line_width', $this->config->doctable->{$section}, 0.2));
        $this->pdf->SetDrawColorArray(
            $this->convertColor(
                StdLib::getParam('draw_color', $this->config->doctable->{$section}, 'black')));
        $this->pdf->SetFillColorArray(
            $this->convertColor(
                StdLib::getParam('fill_color', $this->config->doctable->{$section}, 'white')));
        $this->pdf->SetTextColorArray(
            $this->convertColor(
                StdLib::getParam('text_color', $this->config->doctable->{$section}, 'black')));
    }
}