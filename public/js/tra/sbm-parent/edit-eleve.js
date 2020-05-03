/**
 * scripts de la page de sbm-parent/index/edit-eleve.phtml
 * 
 * @project sbm
 * @filesource edit-eleve.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 avr. 2020
 * @version 2020-2.6.0
 */
var texteDemandeR2;
function phraseDemandeR2(state) {
	var div = $("#demandeR2-text");
	if (div.length == 0) {
		$('#r2_demandeR2 fieldset').append(
				'<div id="demandeR2-text"></div>');
	}
	div = $("#r2_demandeR2 #demandeR2-text");
	div.empty();
	if (state) {
		div.append(texteDemandeR2);
	}
}
function montreStationIdR2(state) {
	var div = $("#r2_stationIdR2");
	if (state) {
		div.show();
	} else {
		div.hide();
	}
}

function avecAdressePerso(choisi)
{
	$("#enfant_ap").empty();
	$("#z3cg-r1").empty();
	$("#z3cd-r1").empty();
	if (choisi){
		bloc = $("#content_responsable1").clone();
		bloc.removeAttr("id").appendTo("#z3cd-r1");
		bloc = $("#content_enfant_ap").clone();
		ch = bloc.find('[id]');
		ch.each(function(i) {
			if (this.id) {
				this.id = this.id.replace('enfant_', '');
				this.id = this.id.replace('?', 'enfant-');
			}
		});
		bloc.find('label').prop('for', function(i, oldVal) {
					return oldVal.replace('enfant_', '');					
		});
		bloc.removeAttr("id").appendTo("#enfant_ap");
	}
}

function sansAdressePerso(choisi)
{
	$("#enfant_ap").empty();
	$("#z3cg-r1").empty();
	$("#z3cd-r1").empty();
	if (choisi) {
		bloc = $("#content_responsable1").clone();
		bloc.removeAttr("id").appendTo("#z3cg-r1");
	}
}

// copie / suppression de la div par le bouton radio (#btradioap...)
$(function() {
	// montre ou cache la partie du formulaire concernant l'adresse perso
	$("#btnradioap0").on('click', function() {
		sansAdressePerso($(this).is(':checked'));
	});
	$("#btnradioap1").click(function() {
		avecAdressePerso($(this).is(':checked')); 
			/*bloc = $("#content_responsable1").clone();
			bloc.removeAttr("id").appendTo("#z3cd-r1");
			bloc = $("#content_enfant_ap").clone();
			ch = bloc.find('[id]');
			ch.each(function(i) {
				if (this.id) {
					this.id = this.id.replace('enfant_', '');
					this.id = this.id.replace('?', 'enfant-');
				}
			});
			bloc.find('label').prop('for', function(i, oldVal) {
						return oldVal.replace('enfant_', '');					
			});
			bloc.removeAttr("id").appendTo("#enfant_ap");
		*/
	});
	// adapte le select de la classe à l'établissement
	$("#enfant_etablissementId").change(function(){
		var classeId = $("#enfant_classeId").val();
		var etablissementId = $(this).val();
		$.ajax({
			url : '/sbmajaxparent/getclassesforselect/etablissementId:' + etablissementId,
			dataType : 'json',
			success : function(dataJson) {
				if (dataJson.success == 1) {
					var select = $("#enfant_classeId");
					select.empty();
					$.each(dataJson.data, function(niveau, descripteur){
						var optgroup = $("<optgroup></optgroup>");
						optgroup.attr('label',descripteur.label);
						$.each(descripteur.options, function(id, libelle){
							var option = $("<option></option>");
							option.val(id);
							option.text(libelle);
							optgroup.append(option);
						});
						select.append(optgroup);
					});
				} else {
					alert(dataJson.cr);
				}
			},
			error : function(xhr, ajaxOptions, thrownError) {
				alert(xhr.status + " " + thrownError);
			}
		});
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
			ch = bloc.find('*[for]');
			ch.each(function(i){
				if (this.attributes['for']) {
					this.attributes['for'].nodeValue = 'r2'+this.attributes['for'].nodeValue;
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
				montreStationIdR2(state);
			});
});

$(function() {
	if ($("#btnradioap1").is(':checked')) {
		avecAdressePerso(1);
	} else {
		sansAdressePerso(1);
	}
	if ($("#btnradioga1").is(':checked')) {
		bloc = $("#content_enfant_ga").clone();
		ch = bloc.find('*[id]');
		ch.each(function(i) {
			if (this.id) {
				this.id = 'r2' + this.id;
			}
		});
		ch = bloc.find('*[for]');
		ch.each(function(i){
			if (this.attributes['for']) {
				this.attributes['for'].nodeValue = 'r2'+this.attributes['for'].nodeValue;
			}
		});
		bloc.removeAttr("id").appendTo("#enfant_ga");
	}
	if ($("#r2demandeR2").is(':checked')) {
		phraseDemandeR2(true);
		montreStationIdR2(true);
	} else {
		montreStationIdR2(false);
	}
});

$(function(){
	var r = $('input[type="radio"].input-error').parent().parent().parent();
	r.addClass('input-error');
	var c = $('input[type="checkbox"].input-error').parent().parent().parent();
	c.addClass('input-error');
});

// Gestion des photos
var js_photo = (function() {
	var hasPhoto;
	function setBtnDialogLabel() {
		var label = '<i class="fam-camera-edit"></i> ';
		if (hasPhoto) {
			label = label + 'Changer la photo';
		} else {
			label = label + 'Envoyer une photo';
		}
		$("button[type=button][name=opendialog]").html(label);
	}
	function setHasPhoto(value) {
		hasPhoto = value;
		if (value) {
			$("button[name=supprphoto]").show();
			$("#flashMessenger").hide();
		} else {
			$("button[name=supprphoto]").hide();
			$("#flashMessenger").show();
		}
		setBtnDialogLabel();
	}
	function montreBtnEnvoiPhoto(voir) {
		var d = $("button[name=envoiphoto]");
		if (voir) {
			d.show();
		} else {
			d.hide();
		}
	}
	function montrePhoto(data, success) {
		$("input[type=file][name=filephoto]").val('');
		montreBtnEnvoiPhoto(false);
		$("#wrapper-filephoto ul").remove();
		if (success == 1) {
			$("#wrapper-photo img").attr('src', data);
		} else {
			$("#wrapper-filephoto").append('<ul class="input-error"><li>' + data + '</li></ul>');
		}
	}
	$(document).ready(function($) {
		$("input[type=file][name=filephoto]").change(function() {
			if ($(this).val()) {
				montreBtnEnvoiPhoto(true);
			} else {
				montreBtnEnvoiPhoto(false);
			}
		});
		$("#photo-form-modele").dialog({
			modal : true,
			autoOpen : false,
			heigth : 400,
			width : 600,
			resizable : false,
			title : $("#photo-form-modele").attr('data-title')
		});
		$("button[name=opendialog]").click(function(event) {
			$("#photo-form-modele").dialog('open');
			event.preventDefault();
		});
		$("button[name=closedialog]").click(function(event) {
			$("#photo-form-modele").dialog('close');
			event.preventDefault();
		});
		$("button[type=button][name=envoiphoto]").click(function() {
			var eleveid = $("input[type=hidden][name=eleveId]").val();
			var fd = new FormData(document.querySelector("#formphoto"));
			var containerprogress = $(".photo-progress");
			var progressbar = $('.photo-progressbar');
			containerprogress.show();
			progressbar.css('width','0');
			$.ajax({
				xhr: function()
				  {
				    var xhr = new window.XMLHttpRequest();
				    //Upload progress
				    xhr.upload.addEventListener("progress", function(evt){
				      if (evt.lengthComputable) {
				        var percentComplete = parseInt(100 * evt.loaded / evt.total);
				        progressbar.css('width', percentComplete + '%');
				        progressbar.text(percentComplete + '%');
				      }
				    }, false);
				    //Download progress
				    xhr.addEventListener("progress", function(evt){
				      if (evt.lengthComputable) {
				        var percentComplete = evt.loaded / evt.total;
				        //Do something with download progress
				        console.log(percentComplete);
				      }
				    }, false);
				    return xhr;
				  },
				url : '/sbmajaxeleve/savephoto',
				data : fd,
				processData : false,
				contentType : false,
				type : 'post',
				success : function(data) {
							var retour = $.parseJSON(data);
							if (retour.success == 1) {
								montrePhoto(retour.src, true);
								setHasPhoto(true);
							} else {
								montrePhoto(retour.cr, false);
							}
							$("#photo-form-modele").dialog('close');
							containerprogress.hide();
						},
				error : function(xhr, ajaxOptions, thrownError) {
							alert(xhr.status + " " + thrownError);
						}
			});
		});
		$("button[type=button][name=supprphoto]").click(function() {
			var eleveid = $("input[type=hidden][name=eleveId]").val();
			var fd = new FormData();
			fd.append('eleveId', eleveid);
			$.ajax({
				url : '/sbmajaxeleve/supprphoto',
				data : fd,
				processData : false,
				contentType : false,
				type : 'post',
				success : function(data) {
							var retour = $.parseJSON(data);
							if (retour.success == 1) {
								montrePhoto(retour.src, true);
								setHasPhoto(false);
							} else {
								montrePhoto(retour.cr, false);
							}
						},
				error : function(xhr, ajaxOptions, thrownError) {
							alert(xhr.status + " " + thrownError);
						}
			});
		});
	});
	return {
		"init" : function(hasPhoto) {
			setHasPhoto(hasPhoto);
			montreBtnEnvoiPhoto(false);
		}
	}
})();