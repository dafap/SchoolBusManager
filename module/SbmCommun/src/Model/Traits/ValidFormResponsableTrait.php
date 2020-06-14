<?php
/**
 * Méthodes communes aux formulaires sur les responsables
 *
 * @project sbm
 * @package SbmCommun/Model/Traits
 * @filesource ValidFormResponsableTrait.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Traits;

use SbmCommun\Filter\ZoneAdresse;

trait ValidFormResponsableTrait
{

    public function isValid()
    {
        $valid = parent::isValid() && $this->telephoneValid() &&
            $this->adresseZonneeValid();
        return $valid;
    }

    private function adresseZonneeValid()
    {
        if (! in_array($this->data['communeId'], $this->getCommunesZonees())) {
            // die(var_dump($this->communes_zonees));
            return true;
        }
        $za = new ZoneAdresse();
        $tzonage = $this->db_manager->get('Sbm\Db\Table\Zonage');
        $adresseL1 = $za->filter($this->data['adresseL1']);
        // die($adresseL1);
        if ($tzonage->isAdresseConnue($this->data['communeId'], $adresseL1)) {
            return true;
        }
        if ($this->data['adresseL2']) {
            $adresseL2 = $za->filter($this->data['adresseL2']);
            if ($tzonage->isAdresseConnue($this->data['communeId'], $adresseL2)) {
                return true;
            }
        } else {
            $adresseL2 = '';
        }
        $tzonageindex = $this->db_manager->get('Sbm\Db\Table\ZonageIndex');
        $adresseL1 = explode(' ', $adresseL1);
        $aide = $tzonage->getJoinWith(
            $tzonageindex->selectIn($this->data['communeId'], $adresseL1));
        if ($adresseL2 && ! $aide->count()) {
            $aide = $tzonage->getJoinWith(
                $tzonageindex->selectIn($this->data['communeId'], $adresseL2));
        }
        $n = $aide->count();
        $message = 'Adresse inconnue.';
        if ($n) {
            if ($n > 1) {
                $message .= ' Choisissez éventuellement parmi le propositions suivantes :';
                foreach ($aide as $row) {
                    $message .= '<br>' . $row->nom;
                }
            } else {
                $message .= sprintf(" Voulez-vous dire '%s' ?", $aide->current()->nom);
            }
        }
        $this->get('adresseL1')->setMessages([
            $message
        ]);
        return false;
    }

    private function getCommunesZonees(): array
    {
        if (empty($this->communes_zonees)) {
            $tzonage = $this->db_manager->get('Sbm\Db\Table\Zonage');
            $this->communes_zonees = $tzonage->getCommunesZonees();
        }
        return $this->communes_zonees;
    }

    /**
     * Un des 3 numéros de téléphones doit être renseigné
     *
     * @return bool
     */
    private function telephoneValid(): bool
    {
        $ok = true;
        if (empty($this->data['telephoneF']) && empty($this->data['telephoneP']) &&
            empty($this->data['telephoneT'])) {
            $ok = false;
            $element = $this->get('telephoneT');
            $element->setMessages(
                [
                    'Vous devez indiquer au moins un numéro de téléphone où l\'on pourra vous joindre.'
                ]);
        } elseif ($this->hassbmservicesms) {
            // si un numéro est renseigné, on doit dire s'il peut recevoir des SMS
            if (! empty($this->data['telephoneF']) && ! isset($this->data['smsF'])) {
                $ok = false;
                $element = $this->get('telephoneF');
                $element->setMessages(
                    [
                        'Vous devez indiquer si le responsable accepte de recevoir des SMS sur ce numéro'
                    ]);
            }
            if (! empty($this->data['telephoneP']) && ! isset($this->data['smsP'])) {
                $ok = false;
                $element = $this->get('telephoneP');
                $element->setMessages(
                    [
                        'Vous devez indiquer si le responsable accepte de recevoir des SMS sur ce numéro'
                    ]);
            }
            if (! empty($this->data['telephoneT']) && ! isset($this->data['smsT'])) {
                $ok = false;
                $element = $this->get('telephoneT');
                $element->setMessages(
                    [
                        'Vous devez indiquer si le responsable accepte de recevoir des SMS sur ce numéro'
                    ]);
            }
        }
        return $ok;
    }
}