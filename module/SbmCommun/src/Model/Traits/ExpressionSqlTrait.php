<?php
/**
 * Construction d'expressions SQL
 *
 * @project sbm
 * @package module/SbmCommun/src/Model/Traits
 * @filesource ExpressionSqlTrait.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 mai 2021
 * @version 2021-2.6.1
 */
namespace SbmCommun\Model\Traits;

trait ExpressionSqlTrait
{

    /**
     *
     * @param string $semaine
     *            nom de la colonne semaine
     * @param string $alias
     *            alias de la colonne résultat
     * @return string
     */
    public function getSqlSemaine(string $semaine = 'semaine', string $alias = '')
    {
        return "CONCAT(IF($semaine & 1,'L','-'),IF($semaine & 2,'M','-'),IF($semaine & 4,'M','-')," .
            "IF($semaine & 8,'J','-'),IF($semaine & 16,'V','-'),IF($semaine & 32,'S','-')," .
            "IF($semaine & 64,'D','-'))" . $this->getAlias($alias);
    }

    /**
     *
     * @param string $sens
     *            nom de la colonne sens
     * @param string $alias
     *            alias de la colonne résultat
     * @return string
     */
    public function getSqlSens(string $sens = 'sens', string $alias = '')
    {
        return "IF($sens = 1,'Aller','Retour')" . $this->getAlias($alias);
    }

    /**
     *
     * @param string $moment
     *            nom de la colonne moment
     * @param string $alias
     *            alias de la colonne résultat
     * @return string
     */
    public function getSqlMoment(string $moment = 'moment', string $alias = '')
    {
        return "CASE $moment WHEN 1 THEN 'Matin' WHEN 2 THEN 'Midi' WHEN 3 THEN 'Soir' WHEN 4 THEN 'Après-midi' ELSE 'Dimanche Soir' END";
    }

    /**
     *
     * @param string $ordre
     *            nom de la colonne ordre
     * @param string $alias
     *            alias de la colonne résultat
     * @return string
     */
    public function getSqlOrdre(string $ordre = 'ordre', string $alias = '')
    {
        return "CONCAT('N°',$ordre)" . $this->getAlias($alias);
    }

    /**
     * Renvoie une expression Sql permettant d'afficher la colonne de la forme LMMJV--
     * ligneId hh:mm:ss Aller
     *
     * @param string $semaine
     * @param string $ligneId
     * @param string $horaire
     * @param string $sens
     * @param string $prefix
     * @return string
     */
    public function getSqlSemaineLigneHoraireSens(string $semaine = 'semaine',
        string $ligneId = 'ligneId', string $horaire = 'horaire', string $sens = 'sens',
        string $prefix = '')
    {
        if ($prefix) {
            $prefix = rtrim($prefix, '.') . '.';
        }
        $expr = implode(',',
            [
                $this->getSqlSemaine($prefix . $semaine),
                $prefix . $ligneId,
                "DATE_FORMAT($prefix$horaire,'%H:%i')",
                $this->getSqlSens($prefix . $sens)
            ]);
        return 'CONCAT_WS(" ",' . $expr . ')';
    }

    public function getSqlDesignationService(string $ligneId = 'ligneId',
        string $sens = 'sens', string $moment = 'moment', string $ordre = 'ordre')
    {
        $expr = implode(',',
            [
                $ligneId,
                $this->getSqlSens($sens),
                $this->getSqlMoment($moment),
                $this->getSqlOrdre($ordre)
            ]);
        return 'CONCAT_WS(" - ",' . $expr . ')';
    }

    /**
     * Format du résultat : ligneId Moment Semaine Sens Ordre
     *
     * @param string $ligneId
     *            nom de la colonne dans la table ou dans la requête
     * @param string $sens
     *            nom de la colonne dans la table ou dans la requête
     * @param string $moment
     *            nom de la colonne dans la table ou dans la requête
     * @param string $ordre
     *            nom de la colonne dans la table ou dans la requête
     * @param string $semaine
     *            nom de la colonne dans la table ou dans la requête
     * @param string $prefix
     *            alias de la table ou de la requête servant de préfixe aux noms de
     *            colonne
     * @return string Expression Sql
     */
    public function getSqlChoixService(string $ligneId = 'ligneId', string $sens = 'sens',
        string $moment = 'moment', string $ordre = 'ordre', string $semaine = 'semaine',
        string $prefix = '')
    {
        if ($prefix) {
            $prefix = rtrim($prefix, '.') . '.';
        }
        $expr = implode(',',
            [
                $prefix . $ligneId,
                $this->getSqlMoment($prefix . $moment),
                $this->getSqlSens($prefix . $sens),
                $this->getSqlOrdre($prefix . $ordre),
                $this->getSqlSemaine($prefix . $semaine)
            ]);
        return 'CONCAT_WS(" ",' . $expr . ')';
    }

    /**
     * Renvoie un identifiant du service par encodage.
     * Les colonnes ligneId, sens, moment et ordre sont concaténées avec pour séparateur
     * le caractère |
     * Si l'encodage est réalisé à partir d'une fiche de la table affectations, on précise
     * alors si il concerne le service 1 ou le service 2
     *
     * @param string $prefix
     * @param bool $fromAffectation
     * @param int $num_ligne
     * @return string
     */
    public function getSqlEncodeServiceId(string $prefix = '',
        bool $fromAffectation = false, int $num_ligne = 1)
    {
        if ($prefix) {
            $prefix = rtrim($prefix, '.') . '.';
        }
        if ($fromAffectation) {
            return sprintf(
                'CONCAT_WS("|",%1$sligne%2$dId,%1$ssensligne%2$d,%1$smoment,%1$sordreligne%2$d)',
                $prefix, $num_ligne);
            // return 'CONCAT_WS(\'|\',ligne1Id,sensligne1,moment,ordreligne1)';
        }
        return sprintf('CONCAT_WS("|",%1$sligneId,%1$ssens,%1$smoment,%1$sordre)', $prefix);
        // return 'CONCAT_WS(\'|\',ligneId,sens,moment,ordre)';
    }

    private function getAlias(string $alias)
    {
        return $alias ? " AS $alias" : '';
    }
}