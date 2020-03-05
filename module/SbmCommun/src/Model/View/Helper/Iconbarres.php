<?php
/**
 * Icone carré composé de 4 barres colorées
 *
 * version TRANSDEV ALBERTVILLE
 *
 * @project sbm
 * @package SbmCommun/Model/View/Helper
 * @filesource Iconbarres.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Iconbarres extends AbstractHelper
{

    public function __invoke()
    {
        return <<<EOT
<span><svg id="icon-2bf96419757c17ad71432777342184fb"
		class="c-Iconbarres" xmlns="http://www.w3.org/2000/svg" width="35.94"
		height="28.57" viewBox="0 0 35.94 28.57">
		<path
			d="M34.37 3.15H1.57a1.58 1.58 0 0 1 0-3.15h32.8a1.58 1.58 0 0 1 0 3.15z"
			fill="#00a2dd"></path>
		<path
			d="M34.37 11.62H1.66a1.58 1.58 0 1 1 0-3.15h32.71a1.58 1.58 0 0 1 0 3.15z"
			fill="#015291"></path>
		<path
			d="M34.37 20.09H1.57a1.57 1.57 0 0 1 0-3.14h32.8a1.57 1.57 0 1 1 0 3.14z"
			fill="#0084a5"></path>
		<path
			d="M34.37 28.57H1.57a1.58 1.58 0 0 1 0-3.15h32.8a1.58 1.58 0 0 1 0 3.15z"
			fill="#f08700"></path></svg></span>
EOT;
    }
}
