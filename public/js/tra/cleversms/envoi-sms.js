/**
 * Scripts de la page de sbm-clever-sms/index/envoi-sms.phtml
 * 
 * @project sbm
 * @filesource envoi-sms.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 avr. 2019
 * @version 2019-2.5.0
 */
var js_envoi_sms = (function() {
	var maxlength;
	function MaxLengthTextarea(objettextarea) {
		var longueur = objettextarea.value.length;
		var reste = maxlength - longueur;
		$('#sms-body-msg').empty();
		if (reste > 1) {
			$('#sms-body-msg').append('<span class="warning">Il reste ' + reste + ' caractères.</span>');
		} else {
			if (reste > 0) {
				$('#sms-body-msg').append('<span class="alert">Il reste 1 caractère.</span>');
			} else {
				$('#sms-body-msg')
						.append(
								'<span class="exces">Longueur maximale atteinte.</span>');
			}
		}
		if (reste < 0) {
			objettextarea.value = objettextarea.value.substring(0, maxlength);
		}
	}
	// mise en place des listeners
	$(document).ready(function() {
		$("#sms-body").keyup(function(e) {
			MaxLengthTextarea(this);
		});
	});
	// méthodes publiques
	return {
		"init" : function(longueur) {
			maxlength = longueur;
		}
	}
})();