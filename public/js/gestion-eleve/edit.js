/**
 * Ensemble des scripts des pages de sbm-gestion/eleve/eleve-edit.phtml
 * 
 * @project sbm
 * @filesource edit.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 jan. 2019
 * @version 2019-2.4.6
 */

var js_edit = (function() {
	var tarifs = [];
	var disableAccordR1;
	var disableAccordR2;
	var subventionR1;
	var subventionR2;
	var hasPhoto;
	function initAccord() {
		$("input[name=subventionR1][type=hidden]").val(js_edit.subventionR1);
		$("input[name=subventionR2][type=hidden]").val(js_edit.subventionR2);
		if ($("#demander1radio1").is(":checked")) {
			$("#eleve-subventionR1").removeAttr("disabled");
			if (js_edit.disableAccordR1) {
				$("#eleve-accordR1").attr("disabled", true);
				$("input[name=accordR1][type=hidden]").val(1);
			} else {
				$("#eleve-accordR1").removeAttr("disabled");
				$("input[name=accordR1][type=hidden]").val(0);
			}
		} else {
			var checked = $("#eleve-accordR1").is(":checked");
			$("input[name=accordR1][type=hidden]").val(checked ? 1 : 0);
			$("#eleve-accordR1").attr("disabled", true);
			$("#eleve-subventionR1").attr("disabled", true);
		}
		if ($("#demander2radio1").is(":checked")) {
			$("#eleve-subventionR2").removeAttr("disabled");
			if (js_edit.disableAccordR2) {
				$("#eleve-accordR2").attr("disabled", true);
				$("input[name=accordR2][type=hidden]").val(1);
			} else {
				$("#eleve-accordR2").removeAttr("disabled");
				$("input[name=accordR2][type=hidden]").val(0);
			}
		} else {
			var checked = $("#eleve-accordR2").is(":checked");
			$("input[name=accordR2][type=hidden]").val(checked ? 1 : 0);
			$("#eleve-accordR2").attr("disabled", true);
			$("#eleve-subventionR2").attr("disabled", true);
		}
	}
	function montreBtnEnvoiPhoto(voir) {
		var d = $("button[name=envoiphoto]");
		if (voir) {
			d.show();
		} else {
			d.hide();
		}
	}
	function montreDebutFin(ancomplet) {
		if (ancomplet) {
			$("#wrapper-dateDebut").hide();
			$("#wrapper-dateFin").hide();
		} else {
			$("#wrapper-dateDebut").show();
			$("#wrapper-dateFin").show();
		}
	}
	function majMontantInscription(ancomplet) {
		$("#tabs-3-montant").empty();
		if (ancomplet) {
			$("#eleve-tarifId").val(1);
			// $("#tabs-3-montant").html(js_edit.tarifs[1] + ' €');
		} else {
			$("#eleve-tarifId").val(3);
			// $("#tabs-3-montant").html(js_edit.tarifs[3] + ' €');
		}
	}
	function montreMotifDerogation(derogation) {
		if (derogation) {
			$("#wrapper-motifDerogation").show();
		} else {
			$("#wrapper-motifDerogation").hide();
		}
	}
	function montreOngletGa(voir) {
		if (voir) {
			$("#tabs li").eq(1).show();
		} else {
			$("#tabs li").eq(1).hide();
		}
	}
	// pour les fonctions suivantes r prend la valeur 'r1' ou 'r2'
	function montreDemande(r) {
		if ($("#demande" + r + "radio0").is(":checked")) {
			$("#block-demande" + r).hide();
		} else {
			$("#block-demande" + r).show();
		}
		var accordElementName = "#eleve-accord" + r.toUpperCase();
		if ($("#demande" + r + "radio1").is(":checked")) {
			var accord = $(accordElementName).is(':checked');
			var disableAccord = r == 'r1' ? js_edit.disableAccordR1
					: js_edit.disableAccordR2;
			if (disableAccord) {
				$(accordElementName).attr("disabled", true);
			} else {
				$(accordElementName).removeAttr("disabled");
			}
			$("#eleve-subvention" + r.toUpperCase()).removeAttr("disabled");
			montreBlockAffectation(accord, r);
		} else {
			if (r.toUpperCase() == 'R1') {
				js_edit.setAccordR1($(accordElementName).is(':checked'));
			} else {
				js_edit.setAccordR2($(accordElementName).is(':checked'));
			}
			$(accordElementName).attr("disabled", true);
			$("#eleve-subvention" + r.toUpperCase()).attr("disabled", true);
			$("#block-affectations" + r + " i").hide();
		}
	}
	function montreMotifRefus(accord, subvention, r) {
		if (accord) {
			if (subvention) {
				$("#wrapper-motifRefus" + r).show();
			} else {
				$("#wrapper-motifRefus" + r).hide();
			}
		} else {
			$("#wrapper-motifRefus" + r).show();
		}
	}
	function montreBlockAffectation(accord, r) {
		if (accord) {
			$("#block-affectations" + r + " i").show();
		} else {
			$("#block-affectations" + r + " i").hide();
		}
	}
	function montrePhoto(data, success) {
		// vide les 2 controles en même temps
		$("input[type=file][name=filephoto]").val('');
		// cache le bouton envoi
		montreBtnEnvoiPhoto(false);
		// supprime le message d'erreur
		$("#wrapper-filephoto ul").remove();
		if (success == 1) {
			$("#wrapper-photo img").attr('src', data);
		} else {
			$("#wrapper-filephoto").append('<ul><li>' + data + '</li></ul>');
		}
	}
	function montreResponsable(r, data) {
		var oresponsable = $.parseJSON(data);
		var part_html;
		var responsable;
		responsable = oresponsable.titre + ' ' + oresponsable.nom + ' '
				+ oresponsable.prenom;
		if (oresponsable.nom2.trim() != '') {
			responsable += ' ou ' + oresponsable.titre2 + ' '
					+ oresponsable.nom2 + ' ' + oresponsable.prenom2;
		}
		$("#" + r + "-ligne1").html(responsable);
		$("#" + r + "-ligne2").html(
				oresponsable.adresseL1 + ' ' + oresponsable.adresseL2);
		$("#" + r + "-ligne3").html(
				oresponsable.codePostal + ' ' + oresponsable.commune);
		if (!!oresponsable.email) {
			part_html = [];
			part_html.push('email: ' + oresponsable.email);
			part_html.push('<div id="email' + r
					+ '" style="display: inline; margin-left: 5px;">');
			part_html.push('<input type="hidden" name="email' + r + '" value="'
					+ oresponsable.email + '">');
			part_html.push('<input type="hidden" name="responsable' + r
					+ '" value="' + responsable + '">');
			part_html.push('<input type="hidden" name="group" value="'
					+ URL_ICI + '">');
			part_html
					.push('<input type="submit" name="ecrire'
							+ r
							+ '" class="fam-email" title="Envoyer un email" value="" formaction="/gestion/eleve/responsable-mail">');
			part_html.push('</div>');
			$("#" + r + "-ligne4").html(part_html.join('\n'));
		} else {
			$("#" + r + "-ligne4").empty();
		}
		part_html = [ 'Tél. ' ];
		if (!!oresponsable.telephoneF) {
			part_html.push(oresponsable.telephoneF);
		}
		if (!!oresponsable.telephoneP) {
			part_html.push(oresponsable.telephoneP);
		}
		if (!!oresponsable.telephoneT) {
			part_html.push(oresponsable.telephoneT);
		}
		$("#" + r + "-ligne5").html(part_html.join(' '));
	}

	$(document).ready(function($) {
			$("#duplicatamoins").click(function() {
					$.ajax({
						url : '/sbmajaxeleve/decrementeduplicata/eleveId:'
																+ ELEVE_ID,
						dataType : 'json',
						success : function(data) {
										var myid = "#nbduplicata";
										var duplicata = data.duplicata;
										$(myid).empty();
										$(myid).append(duplicata.toString());
								},
						error : function(xhr,ajaxOptions,thrownError) {
										alert(xhr.status+ ' ' + thrownError);
								}
						});
			});
			$("#duplicataplus").click(function() {
				$.ajax({
					url : '/sbmajaxeleve/incrementeduplicata/eleveId:'
															+ ELEVE_ID,
					dataType : 'json',
					success : function(data) {
									var myid = "#nbduplicata";
									var duplicata = data.duplicata;
									$(myid).empty();
									$(myid).append(duplicata.toString());
							},
					error : function(xhr,ajaxOptions,thrownError) {
									alert(xhr.status+ ' ' + thrownError);
							}
					});
		    });
			$("#eleve-anneeComplete").click(function() {
						montreDebutFin($(this).is(":checked"));
						majMontantInscription($(this).is(":checked"));
			});
			$("#eleve-derogation").click(function() {
						montreMotifDerogation($(this).is(":checked"));
			});
			$("#eleve-ga").click(function() {
						montreOngletGa($(this).is(":checked"));
			});
			$("#fiche-inner input[name=demandeR1]").click(function() {
						montreDemande('r1');
			});
			$("#fiche-inner input[name=demandeR2]").click(function() {
						montreDemande('r2');
			});
			$("#eleve-accordR1").click(function() {
						var action = ($(this).is(':checked')) ? 'check' : 'uncheck';
						var subvention = $("#eleve-subventionR1").is(':checked');
						$.ajax({
							url : '/sbmajaxeleve/' + action
													+ 'accordR1/eleveId:'
													+ ELEVE_ID,
							success : function(data) {
										montreMotifRefus(
													action == 'check',
													subvention, 'r1');
										montreBlockAffectation(
													action == 'check',
													'r1');
									},
							error : function(xhr,
											ajaxOptions,
											thrownError) {
												alert(xhr.status + ' ' + thrownError);
									}
							});
			});
			$("#eleve-subventionR1").click(function() {
						var accord = $("#eleve-accordR1").is(':checked');
						var subvention = $(this).is(':checked');
						js_edit.setSubventionR1(subvention);
						montreMotifRefus(accord, subvention, 'r1');
			});
			$("#eleve-accordR2").click(function() {
						var action = ($(this).is(':checked')) ? 'check' : 'uncheck';
						var subvention = $("#eleve-subventionR2").is(':checked');
						$.ajax({
							url : '/sbmajaxeleve/' + action
										+ 'accordR2/eleveId:'
										+ ELEVE_ID,
							success : function(data) {
										montreMotifRefus(
											action == 'check', subvention, 'r2');
										montreBlockAffectation(
											action == 'check', 'r2');
									},
							error : function(xhr, ajaxOptions, thrownError) {
										alert(xhr.status + ' ' + thrownError);
									}
						});
			});
			$("#eleve-subventionR2").click(function() {
						var accord = $("#eleve-accordR2").is(':checked');
						var subvention = $(this).is(':checked');
						js_edit.setSubventionR1(subvention);
						montreMotifRefus(accord, subvention, 'r2');
			});
			$("input[type='text'][name^='distanceR']").dblclick(function() {
						$(this).css("cursor", "wait");
						var myid = '#' + $(this).attr('id');
						var name = $(this).attr('name');
						var n = 1;
						if (name.indexOf('1') == -1) {
							n = 2;
						}
						var id = '#eleve-responsable' + n + 'Id';
						var responsableid = $(id).val();
						var etablissementid = $("#eleve-etablissementId").val();
						var args = 'etablissementId:'
									+ etablissementid
									+ '/responsableId:'
									+ responsableid;
						$.ajax({
							url : '/sbmajaxeleve/donnedistance/' + args,
							type : 'GET',
							dataType : 'json',
							success : function(data) {$(myid).val(
										data.distance);
										$(myid).css('cursor','auto');
									},
							error : function(xhr, ajaxOptions, thrownError) {
										alert(xhr.status + " " + thrownError);
										$(myid).css('cursor', 'auto');
									}
							});
			});
			$("#eleve-responsable1Id").change(function() {
						var responsableid = $(this).val();
						$.ajax({
							url : '/sbmajaxeleve/getresponsable/responsableId:'
									+ responsableid,
							success : function(data) {
										montreResponsable('r1', data);
									},
							error : function(xhr, ajaxOptions, thrownError) {
										alert(xhr.status + " " + thrownError);
									}
							});
			});
			$("#eleve-responsable2Id").change(function() {
						var responsableid = $(this).val();
						$.ajax({
							url : '/sbmajaxeleve/getresponsable/responsableId:'
									+ responsableid,
							success : function(data) {
										montreResponsable('r2', data);
									},
							error : function(xhr, ajaxOptions, thrownError) {
										alert(xhr.status + " " + thrownError);
									}
						});
			});
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
											js_edit.setHasPhoto(true);
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
											js_edit.setHasPhoto(false);
										} else {
											montrePhoto(retour.cr, false);
										}
									},
							error : function(xhr, ajaxOptions, thrownError) {
										alert(xhr.status + " " + thrownError);
									}
						});
			});
			$("#tabs").on('click', "i[data-button=btnaffectation]", function() {
						var trajet = $(this).attr('data-trajet');
						var respid = '#eleve-responsable' + trajet + 'Id';
						var href = '/sbmajaxeleve/formaffectation/eleveId:'
									+ ELEVE_ID
									+ '/trajet:'
									+ trajet
									+ $(this).attr('data-href')
									+ '/responsableId:';
						href = href.concat($(respid).val());
						$("#winpopup").dialog({
										draggable : true,
										modal : true,
										autoOpen : false,
										height : 400,
										width : 600,
										resizable : false,
										title : $(this).attr('title')
						});
						$("#winpopup").load(href);
						$("#winpopup").dialog("open");
						return false;
			});
			$("#eleve-btnpaiement").click( function() {
						var href = '/sbmajaxeleve/formpaiement/eleveId:' + ELEVE_ID;
						$("#winpopup").dialog({
										draggable : true,
										modal : true,
										autoOpen : false,
										height : 400,
										width : 600,
										resizable : false,
										title : 'Destinataire de la facture'
						});
						$("#winpopup").load(href);
						$("#winpopup").dialog("open");
						return false;
			});
	});
	return {
		"init" : function(disableAccordR1, disableAccordR2, subventionR1,
				subventionR2, tarifs, hasPhoto) {
			js_edit.setHasPhoto(hasPhoto);
			js_edit.setDisableAccordR1(disableAccordR1);
			js_edit.setDisableAccordR2(disableAccordR2);
			js_edit.setSubventionR1(subventionR1);
			js_edit.setSubventionR2(subventionR2);
			js_edit.tarifs = tarifs;
			$("#tabs").tabs();
			montreDebutFin($("#eleve-anneeComplete").is(":checked"));
			// majMontantInscription($("#eleve-anneeComplete").is(":checked"));
			montreMotifDerogation($("#eleve-derogation").is(":checked"));
			montreOngletGa($("#eleve-ga").is(":checked"));
			montreMotifRefus($("#eleve-accordR1").is(":checked"), 
					$("#eleve-subventionR1").is(":checked"), 'r1');
			montreMotifRefus($("#eleve-accordR2").is(":checked"), 
					$("#eleve-subventionR2").is(":checked"), 'r2');
			// montreBlockAffectation($("#eleve-accordR1").is(":checked"),'r1');
			// montreBlockAffectation($("#eleve-accordR2").is(":checked"),'r2');
			montreDemande('r1');
			montreDemande('r2');
			montreBtnEnvoiPhoto(false);
			initAccord();
			$.ajax({
				url : '/sbmajaxeleve/getresponsable/responsableId:'
						+ $("#eleve-responsable1Id").val(),
				success : function(data) {
					montreResponsable('r1', data);
				},
				error : function(xhr, ajaxOptions, thrownError) {
					alert(xhr.status + " " + thrownError);
				}
			});
			if ($("#eleve-responsable2Id").val()) {
				$.ajax({
					url : '/sbmajaxeleve/getresponsable/responsableId:'
							+ $("#eleve-responsable2Id").val(),
					success : function(data) {
						montreResponsable('r2', data);
					},
					error : function(xhr, ajaxOptions, thrownError) {
						alert(xhr.status + " " + thrownError);
					}
				});
				montreDemande('r1');
				montreDemande('r2');
			}
		},
		"setDisableAccordR1" : function(disableAccordR1) {
			js_edit.disableAccordR1 = disableAccordR1;
		},
		"setDisableAccordR2" : function(disableAccordR2) {
			js_edit.disableAccordR2 = disableAccordR2;
		},
		"setAccordR1" : function(accordR1) {
			$("input[name=accordR1][type=hidden]").val(Number(accordR1));
		},
		"setAccordR2" : function(accordR2) {
			$("input[name=accordR2][type=hidden]").val(Number(accordR2));
		},
		"setSubventionR1" : function(subventionR1) {
			subventionR1 = Number(subventionR1);
			js_edit.subventionR1 = subventionR1;
			$("input[name=subventionR1][type=hidden]").val(subventionR1);
		},
		"setSubventionR2" : function(subventionR2) {
			subventionR2 = Number(subventionR2);
			js_edit.subventionR2 = subventionR2;
			$("input[name=subventionR2][type=hidden]").val(subventionR2);
		},
		"setHasPhoto" : function(hasPhoto) {
			js_edit.hasPhoto = hasPhoto;
			if (hasPhoto) {
				$("button[name=supprphoto]").show();
			} else {
				$("button[name=supprphoto]").hide();
			}
		},
		"majBlockAffectations" : function(trajet) {
			$('html', 'body').css('cursor', 'auto');
			var args1 = 'eleveId:' + ELEVE_ID + '/identite:' + IDENTITE + '/trajet:' + trajet;
			$.ajax({
				url : '/sbmajaxeleve/blockaffectations/' + args1,
				type : 'GET',
				dataType : 'html',
				success : function(data) {
					var myid = '#block-affectationsr' + trajet;
					$(myid).empty();
					$(myid).append(data);
					$('html', 'body').css('cursor', 'auto');
				},
				error : function(xhr, ajaxOptions, thrownError) {
					alert(xhr.status + " " + thrownError);
					$('html', 'body').css('cursor', 'auto');
				}
			});
			var args2 = 'eleveId:' + ELEVE_ID + '/trajet:' + trajet;
			var args3 = "input[name=accordR" + trajet + "][type=hidden]";
			$.ajax({
				url : '/sbmajaxeleve/enableaccordbutton/' + args2,
				dataType : 'json',
				success : function(dataJson) {
					var myid = "#eleve-accordR" + trajet;
					if (dataJson.enable) {
						$(myid).removeAttr('disabled');
						$(args3).val(0);
					} else {
						$(myid).attr('disabled', 'disabled');
						$(args3).val(1);
					}
				},
				error : function(xhr, ajaxOptions, thrownError) {
					alert(xhr.status + " " + thrownError);
				}
			});
		},
		"majPaiement" : function(gratuit) {
			var libelle;
			switch (gratuit) {
			case '0':
				libelle = 'Famille';
				break;
			case '1':
				libelle = 'Gratuit';
				break;
			case '2':
				libelle = 'Organisme';
				break;
			}
			$("#inner-paiement").empty();
			$("#inner-paiement").append(libelle);
		}
	}
})();