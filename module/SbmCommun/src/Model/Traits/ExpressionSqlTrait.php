<?php
/**
 * Construction d'expressions SQL
 *
 * @project sbm
 * @package module/SbmCommun/src/Model/Traits
 * @filesource ExpressionSqlTrait.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 mars 2020
 * @version 2020-2.6.0
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
        return "CASE $moment WHEN 1 THEN 'Matin' WHEN 2 THEN 'Midi' ELSE 'Soir' END";
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
     * @param string $alias
     * @return string
     */
    public function getSqlSemaineLigneHoraireSens(string $semaine = 'semaine',
        string $ligneId = 'ligneId', string $horaire = 'horaire', string $sens = 'sens', string $alias = '')
    {
        $expr = implode(',',
            [
                $this->getSqlSemaine($semaine),
                "' '",
                $ligneId,
                "' '",
                "DATE_FORMAT($horaire,'%H:%i')",
                "' '",
                $this->getSqlSens($sens)
            ]);
        return 'CONCAT(' . $expr . ')';
    }

    public function getSqlDesignationService(string $ligneId = 'ligneId', string $sens = 'sens',
        string $moment = 'moment', string $ordre = 'ordre')
    {
        $expr = implode(',',
            [
                $ligneId,
                "' - '",
                $this->getSqlSens($sens),
                "' - '",
                $this->getSqlMoment($moment),
                "' - '",
                $this->getSqlOrdre($ordre)
            ]);
        return 'CONCAT(' . $expr . ')';
    }

    private function getAlias(string $alias)
    {
        return $alias ? " AS $alias" : '';
    }
}