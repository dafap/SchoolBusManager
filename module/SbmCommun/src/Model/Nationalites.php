<?php
/**
 * Gestion de la liste des nationalites
 *
 * @project sbm
 * @package SbmCommun/src/Model
 * @filesource Nationalites.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 oct. 2020
 * @version 2020-2.6.1
 */
namespace SbmCommun\Model;

use SbmBase\Model\StdLib;

class Nationalites
{

    public static function getNationalites()
    {
        return [
            'FR' => 'Française',
            'CH' => 'Suisse',
            'BE' => 'Belge',
            'DE' => 'Allemande',
            'IT' => 'Italienne',
            'AF' => 'Afghane',
            'AL' => 'Albanaise',
            'DZ' => 'Algerienne',
            'US' => 'Americaine',
            'AD' => 'Andorrane',
            'AO' => 'Angolaise',
            'AG' => 'Antiguaise et barbudienne',
            'AR' => 'Argentine',
            'AM' => 'Armenienne',
            'AU' => 'Australienne',
            'AT' => 'Autrichienne',
            'AZ' => 'Azerbaïdjanaise',
            'BS' => 'Bahamienne',
            'BH' => 'Bahreinienne',
            'BD' => 'Bangladaise',
            'BB' => 'Barbadienne',
            'BZ' => 'Belizienne',
            'BJ' => 'Beninoise',
            'BT' => 'Bhoutanaise',
            'BY' => 'Bielorusse',
            'MM' => 'Birmane',
            'GW' => 'Bissau-Guinéenne',
            'BO' => 'Bolivienne',
            'BA' => 'Bosnienne',
            'BW' => 'Botswanaise',
            'BR' => 'Bresilienne',
            'UK' => 'Britannique',
            'BN' => 'Bruneienne',
            'BG' => 'Bulgare',
            'BF' => 'Burkinabe',
            'BI' => 'Burundaise',
            'KH' => 'Cambodgienne',
            'CM' => 'Camerounaise',
            'CA' => 'Canadienne',
            'CV' => 'Cap-verdienne',
            'CF' => 'Centrafricaine',
            'CL' => 'Chilienne',
            'CN' => 'Chinoise',
            'CY' => 'Chypriote',
            'CO' => 'Colombienne',
            'KM' => 'Comorienne',
            'CG' => 'Congolaise',
            'CR' => 'Costaricaine',
            'HR' => 'Croate',
            'CU' => 'Cubaine',
            'CW' => 'Curaçaoane',
            'DK' => 'Danoise',
            'DJ' => 'Djiboutienne',
            'DO' => 'Dominicaine',
            'DM' => 'Dominiquaise',
            'EG' => 'Egyptienne',
            'AE' => 'Emirienne',
            'GQ' => 'Equato-guineenne',
            'EC' => 'Equatorienne',
            'ER' => 'Erythreenne',
            'ES' => 'Espagnole',
            'TL' => 'Est-timoraise',
            'EE' => 'Estonienne',
            'ET' => 'Ethiopienne',
            'FJ' => 'Fidjienne',
            'FI' => 'Finlandaise',
            'GA' => 'Gabonaise',
            'GM' => 'Gambienne',
            'GE' => 'Georgienne',
            'GH' => 'Ghaneenne',
            'GD' => 'Grenadienne',
            'GT' => 'Guatemalteque',
            'GN' => 'Guineenne',
            'GY' => 'Guyanienne',
            'HT' => 'Haïtienne',
            'GR' => 'Hellenique',
            'HN' => 'Hondurienne',
            'HU' => 'Hongroise',
            'IN' => 'Indienne',
            'ID' => 'Indonesienne',
            'IQ' => 'Irakienne',
            'IE' => 'Irlandaise',
            'IS' => 'Islandaise',
            'IL' => 'Israélienne',
            'CI' => 'Ivoirienne',
            'JM' => 'Jamaïcaine',
            'JP' => 'Japonaise',
            'JO' => 'Jordanienne',
            'KZ' => 'Kazakhstanaise',
            'KE' => 'Kenyane',
            'KG' => 'Kirghize',
            'KI' => 'Kiribatienne',
            'KN' => 'Kittitienne-et-nevicienne',
            'XK' => 'Kossovienne',
            'KW' => 'Koweitienne',
            'LA' => 'Laotienne',
            'LS' => 'Lesothane',
            'LV' => 'Lettone',
            'LB' => 'Libanaise',
            'LR' => 'Liberienne',
            'LY' => 'Libyenne',
            'LI' => 'Liechtensteinoise',
            'LT' => 'Lituanienne',
            'LU' => 'Luxembourgeoise',
            'MK' => 'Macedonienne',
            'MY' => 'Malaisienne',
            'MW' => 'Malawienne',
            'MV' => 'Maldivienne',
            'MG' => 'Malgache',
            'ML' => 'Malienne',
            'MT' => 'Maltaise',
            'MA' => 'Marocaine',
            'MH' => 'Marshallaise',
            'MU' => 'Mauricienne',
            'MR' => 'Mauritanienne',
            'MX' => 'Mexicaine',
            'FM' => 'Micronesienne',
            'MD' => 'Moldave',
            'MC' => 'Monegasque',
            'MN' => 'Mongole',
            'ME' => 'Montenegrine',
            'MZ' => 'Mozambicaine',
            'NA' => 'Namibienne',
            'NR' => 'Nauruane',
            'NL' => 'Neerlandaise',
            'NZ' => 'Neo-zelandaise',
            'NP' => 'Nepalaise',
            'NI' => 'Nicaraguayenne',
            'NG' => 'Nigeriane',
            'NE' => 'Nigerienne',
            'KP' => 'Nord-coréenne',
            'NO' => 'Norvegienne',
            'OM' => 'Omanaise',
            'UG' => 'Ougandaise',
            'UZ' => 'Ouzbeke',
            'PK' => 'Pakistanaise',
            'PW' => 'Palau',
            'PS' => 'Palestinienne',
            'PA' => 'Panameenne',
            'PG' => 'Papouane-neoguineenne',
            'PY' => 'Paraguayenne',
            'PE' => 'Peruvienne',
            'PH' => 'Philippine',
            'PL' => 'Polonaise',
            'PR' => 'Portoricaine',
            'PT' => 'Portugaise',
            'QA' => 'Qatarienne',
            'RO' => 'Roumaine',
            'RU' => 'Russe',
            'RW' => 'Rwandaise',
            'LC' => 'Saint-Lucienne',
            'SM' => 'Saint-Marinaise',
            'SX' => 'Saint-Martinoise',
            'VC' => 'Saint-Vincentaise-et-Grenadine',
            'SB' => 'Salomonaise',
            'SV' => 'Salvadorienne',
            'WS' => 'Samoane',
            'ST' => 'Santomeenne',
            'SA' => 'Saoudienne',
            'SN' => 'Senegalaise',
            'SS' => 'Sud-Soudanaise',
            'RS' => 'Serbe',
            'SC' => 'Seychelloise',
            'SL' => 'Sierra-leonaise',
            'SG' => 'Singapourienne',
            'SK' => 'Slovaque',
            'SI' => 'Slovene',
            'SO' => 'Somalienne',
            'SD' => 'Soudanaise',
            'LK' => 'Sri-lankaise',
            'ZA' => 'Sud-africaine',
            'KR' => 'Sud-coréenne',
            'SE' => 'Suedoise',
            'SR' => 'Surinamaise',
            'SZ' => 'Swazie',
            'SY' => 'Syrienne',
            'TJ' => 'Tadjike',
            'TW' => 'Taiwanaise',
            'TZ' => 'Tanzanienne',
            'TD' => 'Tchadienne',
            'CZ' => 'Tcheque',
            'TH' => 'Thaïlandaise',
            'TG' => 'Togolaise',
            'TO' => 'Tonguienne',
            'TT' => 'Trinidadienne',
            'TN' => 'Tunisienne',
            'TM' => 'Turkmene',
            'TR' => 'Turque',
            'TV' => 'Tuvaluane',
            'UA' => 'Ukrainienne',
            'UY' => 'Uruguayenne',
            'VA' => 'Vaticane',
            'VU' => 'Vanuatuane',
            'VE' => 'Venezuelienne',
            'VN' => 'Vietnamienne',
            'YE' => 'Yemenite',
            'ZM' => 'Zambienne',
            'ZW' => 'Zimbabweenne'
        ];
    }

    public static function decodeNationalite(string $code)
    {
        return StdLib::getParam($code, self::getNationalites(), 'inconnue');
    }

    public static function encodeNationalite(string $nationalite) {
        return StdLib::getParam($nationalite, array_flip(self::getNationalites()), '??');
    }
}