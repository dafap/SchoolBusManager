<?php
/**
 * Renvoie le résultat de la requête (table ou requête SQL) liée au document
 *
 * @project sbm
 * @package
 * @filesource DataTableTrait.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 oct. 2020
 * @version 2020-2.6.1
 */
namespace SbmPdf\Model\Traits;

use SbmBase\Model\StdLib;

trait DataTableTrait
{

    protected function getData($force = false)
    {
        if ($force || empty($this->data)) {
            $this->data = [];
            if ($this->getRecordSourceType() == 'T') {
                $this->fromTable($this->getConfig('doctable', 'columns', []));
            } else {
                $this->fromQuery($this->getConfig('doctable', 'columns', []));
            }
        }
        return $this->data;
    }

    /**
     * Lorsque la source est une table
     *
     * @param array $table_columns
     *            description des colonnes
     */
    private function fromTable($table_columns)
    {
        $table = $this->getRecordSourceTable();
        // si la description des colonnes est vide, on configure toutes les
        // colonnes de la source
        if (empty($table_columns)) {
            $ordinal_position = 1;
            foreach ($table->getColumnsNames() as $column_name) {
                $column = require (__DIR__ . '/default/doccolumns.inc.php');
                $column['thead'] = $column['tbody'] = $column_name;
                $column['ordinal_position'] = $ordinal_position ++;
                $table_columns[] = $column;
            }
            $this->config['doctable']['columns'] = $table_columns;
        }
    }


    private function configTableDefault($table)
    {
        $ordinal_position = 1;
        foreach ($table->getColumnsNames() as $column_name) {
            $column = require (__DIR__ . '/default/doccolumns.inc.php');
            $column['thead'] = $column['tbody'] = $column_name;
            $column['ordinal_position'] = $ordinal_position ++;
            $table_columns[] = $column;
        }
        $this->config['doctable']['columns'] = $table_columns;
    }

    /**
     * Lorsque la source est une reqiête SQL
     *
     * @param array $table_columns
     *            description des colonnes
     */
    private function fromQuery($table_columns)
    {
        ;
    }

    private function test()
    {
        //table
        $columnEffectif = false;
        foreach ($table_columns as &$column) {
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
            // repère les colonnes d'effectifs
            if (preg_match('/%(.*)%/', $column['tbody'])) {
                $columnEffectif = true;
            }
            unset($column);
        }
        $effectifClass = null;
        //query
        $effectifColumns = [];
        foreach ($table_columns as &$column) {
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
            // on relève les colonnes d'effectifs et on met false à leur place
            // dans $column['tbody'] pour ne pas rechercher la valeur dans la
            // requête.
            $matches = [];
            if (preg_match('/^%(.*)%$/', $column['tbody'], $matches)) {
                $effectifColumns[] = $matches[1];
                $column['tbody'] = false;
            } else {
                $columns[] = $column['tbody'];
            }
        }
    }

    protected function getDataForTable($ordinal_table = 1, $force = false)
    {
        if ($force || empty($this->data[$ordinal_table])) {
            $this->data[$ordinal_table] = [];
            // lecture de la description des colonnes
            $table_columns = $this->getConfig('doctable', 'columns', []);

            if ($this->getRecordSourceType() == 'T') {
                /**
                 * POUR LES SOURCES qui sont des TABLES ou des VUES La source doit être
                 * enregistrée dans le ServiceManager (table ou vue MySql) sinon exception
                 */
                $table = $this->getRecordSourceTable();

                // si la description des colonnes est vide, on configure toutes les
                // colonnes de la
                // source
                if (empty($table_columns)) {
                    $ordinal_position = 1;
                    foreach ($table->getColumnsNames() as $column_name) {
                        $column = require (__DIR__ . '/default/doccolumns.inc.php');
                        $column['thead'] = $column['tbody'] = $column_name;
                        $column['ordinal_position'] = $ordinal_position ++;
                        $table_columns[] = $column;
                    }
                    $this->config['doctable']['columns'] = $table_columns;
                }
                // prépare les filtres pour le décodage des données (notamment booléennes)
                $columnEffectif = false;
                foreach ($table_columns as &$column) {
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
                    // repère les colonnes d'effectifs
                    if (preg_match('/%(.*)%/', $column['tbody'])) {
                        $columnEffectif = true;
                    }
                    unset($column);
                }
                $effectifClass = null;
                if ($columnEffectif) {
                    $effectifClassName = $this->getParam('effectifClassName',
                        Columns::getStringEffectifInterface($this->recordSource));
                    if ($this->pdf_manager->get('Sbm\DbManager')->has($effectifClassName)) {
                        $effectifClass = $this->pdf_manager->get('Sbm\DbManager')->get(
                            $effectifClassName);
                        $id = $effectifClass->getIdColumn();
                        $sanspreinscrits = $this->getParam('sanspreinscrits', false);
                        if (method_exists($effectifClass, 'setCaractereConditionnel')) {
                            $caractere = $this->getParam('caractereConditionnel', false);
                            if ($caractere) {
                                $effectifClass->setCaractereConditionnel($caractere)->init(
                                    $sanspreinscrits);
                            } else {
                                // Mauvaise configuration
                                if (getenv('APPLICATION_ENV') == 'development') {
                                    throw new Exception(
                                        "Le paramètre `caractereConditionnel` n'a pas été défini avant l'appel.");
                                }
                                $effectifClass = null;
                            }
                        } else {
                            $effectifClass->init($sanspreinscrits);
                        }
                    }
                }
                // lecture des données et calcul des largeurs de colonnes
                foreach ($table->fetchAll($this->getWhere(), $this->getOrderBy()) as $row) {
                    $ligne = [];
                    foreach ($table_columns as &$column) {
                        try {
                            // var_dump($row->{$column['tbody']}, $column['filter']);
                            $value = StdLib::translateData($row->{$column['tbody']},
                                $column['filter']);
                            switch ($column['nature']) {
                                case 2:
                                    // TODO : non vérifié pour la photo
                                    if ($value) {
                                        $value = '@' . stripslashes($value);
                                    }
                                    break;
                                case 1:
                                    // date : prendre en compte le format
                                    if (! empty($column['format']) &&
                                        stripos('h', $column['format']) !== false) {
                                        $value = DateLib::formatDateTimeFromMysql($value);
                                    } else {
                                        $value = DateLib::formatDateFromMysql($value);
                                    }
                                    break;
                                default:
                                    if ($column['format']) {
                                        $value = sprintf($column['format'], $value);
                                    }
                                    break;
                            }
                            $ligne[] = $value;
                        } catch (\Exception $e) {
                            $value = "0";
                            if ($effectifClass instanceof \SbmGestion\Model\Db\Service\EffectifInterface) {
                                $columntbody = trim($column['tbody'], '%');
                                if (method_exists($effectifClass, $columntbody)) {
                                    $ligne[] = $effectifClass->{$columntbody}($row->{$id});
                                } else {
                                    $ligne[] = 0;
                                }
                            } else {
                                $ligne[] = 0;
                            }
                        }
                        // adapte la largeur de la colonne si nécessaire
                        $value_width = $this->GetStringWidth($value,
                            $this->getConfig('document', 'data_font_family',
                                PDF_FONT_NAME_DATA), '',
                            $this->getConfig('document', 'data_font_size',
                                PDF_FONT_SIZE_DATA));
                        $value_width += $this->cell_padding['L'] + $this->cell_padding['R'];
                        if ($value_width > $column['width']) {
                            $column['width'] = $value_width;
                        }
                        unset($column);
                    }
                    $this->data[$ordinal_table][] = $ligne;
                }
                $this->config['doctable']['columns'] = $table_columns;
            } else {
                /**
                 * POUR LES SOURCES qui sont des REQUETES SQL On essaiera de poser un
                 * effectif sur les colonnes %transportes% et %demandes% à condition qu'on
                 * ait fourni un paramètre 'effectifClassName' correct (cad qu'il existe
                 * une classe `effectifClass` implémentant `EffectifInterface` et
                 * possédant les methodes `tranportes()` et éventuellement `demandes()`.
                 * Pour obtenir des effectifs conditionnels, il faut qu'un paramètre
                 * 'caractereConditionnel' soit passé et que la classe `effectifClass`
                 * présente la méthode `setCaractereConditionnel`. Son appel se fera avant
                 * l'init.
                 */
                $columns = [];
                $effectifColumns = [];
                foreach ($table_columns as &$column) {
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
                    // on relève les colonnes d'effectifs et on met false à leur place
                    // dans $column['tbody'] pour ne pas rechercher la valeur dans la
                    // requête.
                    $matches = [];
                    if (preg_match('/^%(.*)%$/', $column['tbody'], $matches)) {
                        $effectifColumns[] = $matches[1];
                        $column['tbody'] = false;
                    } else {
                        $columns[] = $column['tbody'];
                    }
                }
                if ($effectifColumns) {
                    $effectifClassName = $this->getParam('effectifClassName', false);
                    $effectifClass = null;
                    if ($effectifClassName &&
                        $this->pdf_manager->get('Sbm\DbManager')->has($effectifClassName)) {
                        $effectifClass = $this->pdf_manager->get('Sbm\DbManager')->get(
                            $effectifClassName);
                        if ($effectifClass instanceof \SbmGestion\Model\Db\Service\EffectifInterface) {
                            $id = $effectifClass->getIdColumn();
                            $sanspreinscrits = $this->getParam('sanspreinscrits', false);
                            if (method_exists($effectifClass, 'setCaractereConditionnel')) {
                                $caractere = $this->getParam('caractereConditionnel',
                                    false);
                                if ($caractere) {
                                    $effectifClass->setCaractereConditionnel($caractere)->init(
                                        $sanspreinscrits);
                                } else {
                                    // Mauvaise configuration
                                    if (getenv('APPLICATION_ENV') == 'development') {
                                        throw new Exception(
                                            "Le paramètre `caractereConditionnel` n'a pas été défini avant l'appel.");
                                    }
                                    $effectifClass = null;
                                }
                            } else {
                                $effectifClass->init($sanspreinscrits);
                            }
                        } else {
                            $effectifClass = null;
                        }
                    }
                }
                if (empty($columns)) {
                    $columns[] = Select::SQL_STAR;
                }
                $recordSource = $this->decodeSource(
                    $this->getConfig('document', 'recordSource', ''),
                    $this->pdf_manager->get('SbmAuthentification\Authentication')
                        ->by()
                        ->getUserId());
                $dbAdapter = $this->pdf_manager->get('Sbm\DbManager')->getDbAdapter();
                try {
                    $select = new Select($recordSource);
                    $select->columns($columns)
                        ->where($this->getWhere())
                        ->order($this->getOrderBy());
                    $sqlString = $select->getSqlString($dbAdapter->getPlatform());
                    $rowset = $dbAdapter->query($sqlString,
                        \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
                    if ($rowset->count()) {
                        // si la description des colonnes est vide, on configure toutes
                        // les colonnes de la source
                        if (empty($table_columns)) {
                            $ordinal_position = 1;
                            foreach (array_keys($rowset->current()->getArrayCopy()) as $column_name) {
                                $column = require (__DIR__ . '/default/doccolumns.inc.php');
                                $column['thead'] = $column['tbody'] = $column_name;
                                $column['ordinal_position'] = $ordinal_position ++;
                                $table_columns[] = $column;
                            }
                            $this->config['doctable']['columns'] = $table_columns;
                        }
                        foreach ($rowset as $row) {
                            // $row est un ArrayObject
                            $ligne = [];
                            $idEffectifColumns = 0;
                            for ($key = 0; $key < count($table_columns); $key ++) {
                                // on distingue les colonnes d'effectifs
                                if ($table_columns[$key]['tbody']) {
                                    // ce n'est pas une colonne d'effectif
                                    $value = $row[$table_columns[$key]['tbody']];
                                    // var_dump($value, $table_columns[$key]['filter']);
                                    $value = StdLib::translateData($value,
                                        $table_columns[$key]['filter']);
                                    switch ($table_columns[$key]['nature']) {
                                        case 2:
                                            // TODO : non vérifié pour la photo
                                            if ($value) {
                                                $value = '@' . stripslashes($value);
                                            }
                                            break;
                                        case 1:
                                            // date : prendre en compte le format
                                            if (! empty($table_columns[$key]['format']) &&
                                                stripos('h',
                                                    $table_columns[$key]['format']) !==
                                                false) {
                                                $value = DateLib::formatDateTimeFromMysql(
                                                    $value);
                                            } else {
                                                $value = DateLib::formatDateFromMysql(
                                                    $value);
                                            }
                                            break;
                                        default:
                                            if ($table_columns[$key]['format']) {
                                                $value = sprintf(
                                                    $table_columns[$key]['format'], $value);
                                            }
                                            break;
                                    }
                                } elseif (array_key_exists($idEffectifColumns,
                                    $effectifColumns)) {
                                    // c'est une colonne d'effectif
                                    $method = $effectifColumns[$idEffectifColumns ++];
                                    if ($effectifClass instanceof \SbmGestion\Model\Db\Service\EffectifInterface &&
                                        method_exists($effectifClass, $method)) {
                                        // la configuration est correcte
                                        $value = $effectifClass->{$method}($row->{$id});
                                    } else {
                                        // la configuration est incorrecte
                                        $value = 0;
                                    }
                                } else {
                                    // autres cas
                                    $value = 0;
                                }
                                // reprise du traitement
                                $ligne[] = $value;
                                // adapte la largeur de la colonne si nécessaire
                                $value_width = $this->GetStringWidth($value,
                                    $this->getConfig('document', 'data_font_family',
                                        PDF_FONT_NAME_DATA), '',
                                    $this->getConfig('document', 'data_font_size',
                                        PDF_FONT_SIZE_DATA));
                                $value_width += $this->cell_padding['L'] +
                                    $this->cell_padding['R'];
                                if ($value_width > $table_columns[$key]['width']) {
                                    $table_columns[$key]['width'] = $value_width;
                                }
                            }
                            $this->data[$ordinal_table][] = $ligne;
                            $this->config['doctable']['columns'] = $table_columns;
                        }
                    }
                } catch (\Exception $e) {
                    if (getenv('APPLICATION_ENV') == 'development') {
                        $msg = __METHOD__ . ' - ' . $e->getMessage() . "\n" . $recordSource .
                            "\n" . $e->getTraceAsString();
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
        }
        return $this->data[$ordinal_table];
        return $this->data;
    }
}