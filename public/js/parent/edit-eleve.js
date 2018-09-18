/**
 * scripts de la page de sbm-parent/index/edit-eleve.phtml
 * 
 * @project sbm
 * @filesource edit-eleve.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 01 mai 2018
 * @version 2018-2.4.1
 */
var texteDemandeR2;
function phraseDemandeR2(state) {
	var div = $("#demandeR2-text");
	if (div.length == 0) {
		$('#r2enfant_demandeR2 fieldset').append(
				'<div id="demandeR2-text"></div>');
	}
	div = $("#r2enfant_demandeR2 #demandeR2-text");
	div.empty();
	if (state) {
		div.append(texteDemandeR2);
	}
}
// copie / suppression de la div par le bouton radio (#btradioap...)
$(function() {
	// montre ou cache la partie du formulaire concernant l'adresse perso
	$("#btnradioap0").on('click', function() {
		$("#enfant_ap").empty();
		$("#z3cg-r1").empty();
		$("#z3cd-r1").empty();
		if ($(this).is(':checked')) {
			bloc = $("#content_responsable1").clone();
			bloc.removeAttr("id").appendTo("#z3cg-r1");
		}
	});
	$("#btnradioap1").click(function() {
		$("#enfant_ap").empty();
		$("#z3cg-r1").empty();
		$("#z3cd-r1").empty();
		if ($(this).is(':checked')) {
			bloc = $("#content_responsable1").clone();
			bloc.removeAttr("id").appendTo("#z3cd-r1");
			bloc = $("#content_enfant_ap").clone();
			ch = bloc.find('[id]');
			ch.each(function(i) {
				if (this.id) {
					this.id = this.id.replace('enfant_', '');
					this.id = this.id.replace('?', 'enfant-edit-');
				}
			});
			bloc.find('label').prop('for', function(i, oldVal) {
						return oldVal.replace('enfant_', '');					
			});
			bloc.removeAttr("id").appendTo("#enfant_ap");
		}
	});
	// adapte le select de la commune au code postal pour l'adresse personnelle
	$("#enfant_ap").on(
			'keyup',
			'#codePostalEleve',
			function() {
				var valeur = $('#codePostalEleve').val();
				if (valeur.length == 5) {
					$.ajax({
						url : '/sbmajaxparent/getcommunesforselect/codePostal:'
								+ valeur,
						dataType : 'json',
						success : function(dataJson) {
							$('#communeEleveId').empty();
							if (dataJson.success == 1) {
								$.each(dataJson.data, function(k, d) {
									$('#communeEleveId').append(
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
	// copie / suppression de la div par le bouton radio (#btradioga...)
	// montre ou cache la partie du formulaire concernant la garde alternee
	$("#btnradioga0").on('click', function() {
		$("#enfant_ga").empty();
	});
	$("#btnradioga1").click(function() {
		$("#enfant_ga").empty();
		if ($(this).is(':checked')) {
			bloc = $("#content_enfant_ga").clone();
			ch = bloc.find('*[id]');
			ch.each(function(i) {
				if (this.id) {
					this.id = 'r2' + this.id;
				}
			});
			bloc.removeAttr("id").appendTo("#enfant_ga");
		}
	});
	// adapte le select de la commune au code postal pour le responsable (garde
	// alternée)
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
	if ($("#btnradioap1").is(':checked')) {
		bloc = $("#content_responsable1").clone();
		bloc.removeAttr("id").appendTo("#z3cd-r1");
	} else {
		bloc = $("#content_responsable1").clone();
		bloc.removeAttr("id").appendTo("#z3cg-r1");
	}
	if ($("#btnradioga1").is(':checked')) {
		bloc = $("#content_enfant_ga").clone();
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