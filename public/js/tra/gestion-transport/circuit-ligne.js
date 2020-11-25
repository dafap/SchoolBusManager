/**
 * scripts de la page sbm-gestion/transport/circuit-lite.phtml
 * 
 * @project sbm
 * @filesource gestion-transport/circuit-ligne.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 sept. 2020
 * @version 2020-2.6.1
 */
var js_lignes = (function(){
return {
	"via": function(visible) {
   			if (visible) {
   				$(".via").show();
   			} else {
   				$(".via").hide();
   			}
		
		}
	}
})();