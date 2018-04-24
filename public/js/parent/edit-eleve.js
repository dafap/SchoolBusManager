/**
 * scripts de la page de sbm-parent/index/edit-eleve.phtml
 * 
 * @project sbm
 * @filesource edit-eleve.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 avril 2018
 * @version 2018-2.4.1
 */
var texteDemandeR2;
function phraseDemandeR2(state) {
	var div = $("#demandeR2-text");
	if (div.length == 0) {
		$('#r2enfant_demandeR2 fieldset').append('<div id="demandeR2-text"></div>');
	}
	div = $("#r2enfant_demandeR2 #demandeR2-text");
	if (state) {
		div.append(texteDemandeR2);
	} else {
		div.empty();
	}
}
// copie / suppression de la div par le bouton radio
$(function() {
	// montre ou cache la partie du formulaire concernant la garde alternee
	$("#btnradioga0").on('click', function() {
		$("#enfant_ga").empty();
	});
	$("#btnradioga1").click(function() {
		$("#enfant_ga").empty();
		if ($(this).is(':checked')) {
			bloc = $("#formga").clone();
			ch = bloc.find('*[id]');
			ch.each(function(i) {
				if (this.id) {
					this.id = 'r2' + this.id;
				}
			});
			bloc.removeAttr("id").appendTo("#enfant_ga");
		}
	});
	// adapte le select de la commune au code postal
	$("#enfant_ga").on(
			'keyup',
			'#r2codePostal',
			function() {
				var valeur = $('#r2codePostal').val();
				if (valeur.length == 5) {
					$.ajax({
						url : '/sbmajaxparent/getcommunesforselect/codePostal:'
								+ valeur,
						dataType : 'json',
						success : function(dataJson) {
							$('#r2communeId').empty();
							if (dataJson.success == 1) {
								$.each(dataJson.data, function(k, d) {
									$('#r2communeId').append(
											'<option value="' + d + '">' + k
													+ '</option>');
								});
							}
						},
						error : function(xhr, ajaxOptions, thrownError) {
							alert(xhr.status + " " + thrownError);
						}
					});
				}
			});
	// ajoute un texte si demandeR2 est oui
	$("#enfant_ga").on('click', 'input[type=radio][name=demandeR2]',
			function() {
				var state = $(this).val() == 1;
				phraseDemandeR2(state);
			});
});

$(function() {
	if ($("#btnradioga1").is(':checked')) {
		bloc = $("#formga").clone();
		ch = bloc.find('*[id]');
		ch.each(function(i) {
			if (this.id) {
				this.id = 'r2' + this.id;
			}
		});
		bloc.removeAttr("id").appendTo("#enfant_ga");
	}
	if ($("#r2demandeR2").is(':checked')) {
		phraseDemandeR2(true);
	}
});