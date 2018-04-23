/**
 * Ensemble des scripts des pages de sbm-gestion/eleve/eleve-edit.phtml
 * 
 * @project sbm
 * @filesource edit.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 avr. 2018
 * @version 2018-2.4.1
 */

var js_edit = (function() {
	var tarifs = [];
	var disableAccordR1;
	var disableAccordR2;
	function initAccord() {
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
			$("#tabs-3-montant").html(js_edit.tarifs[1] + ' €');
		} else {
			$("#tabs-3-montant").html(js_edit.tarifs[3] + ' €');
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
		if ($("#demande" + r + "radio1").is(":checked")) {
			var accord = $("#eleve-accord" + r.toUpperCase()).is(':checked');
			var disableAccord = r == 'r1' ? js_edit.disableAccordR1 : js_edit.disableAccordR2;
			if (disableAccord) {
				$("#eleve-accord" + r.toUpperCase()).attr("disabled", true);
			} else {
				$("#eleve-accord" + r.toUpperCase()).removeAttr("disabled");
			}
			$("#eleve-subvention" + r.toUpperCase()).removeAttr("disabled");
			montreBlockAffectation(accord, r);
		} else {
			$("#eleve-accord" + r.toUpperCase()).attr("disabled", true);
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
	function montreResponsable(r, data) {
		var oresponsable = $.parseJSON(data);
		var part_html;
		var responsable;
		responsable = oresponsable.titre + ' ' + oresponsable.nom + ' ' + oresponsable.prenom;
		$("#" + r + "-ligne1").html(responsable);
		$("#" + r + "-ligne2").html(oresponsable.adresseL1 + ' ' + oresponsable.adresseL2);
		$("#" + r + "-ligne3").html(oresponsable.codePostal + ' ' + oresponsable.commune);
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
			part_html.push('<input type="submit" name="ecrire' + r
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
				url : '/sbmajaxeleve/decrementeduplicata/eleveId:' + ELEVE_ID,
				dataType : 'json',
				success : function(data) {
					var myid = "#nbduplicata";
					var duplicata = data.duplicata;
					$(myid).empty();
					$(myid).append(duplicata.toString());
				},
				error : function(xhr, ajaxOptions, thrownError) {
					alert(xhr.status + ' ' + thrownError);
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
			var action = ($(this).is(':checked')) ? 'check': 'uncheck';
			var subvention = $("#eleve-subventionR1").is(':checked');
			$.ajax({
				url : '/sbmajaxeleve/' + action + 'accordR1/eleveId:' + ELEVE_ID,
				success : function(data) {
					montreMotifRefus(action == 'check', subvention, 'r1');
					montreBlockAffectation(action == 'check', 'r1');
				},
				error : function(xhr, ajaxOptions, thrownError) {
					alert(xhr.status + ' ' + thrownError);
				}
			});
		});
		$("#eleve-subventionR1").click(function() {
			var accord = $("#eleve-accordR1").is(':checked');
			var subvention = $(this).is(':checked');
			montreMotifRefus(accord, subvention, 'r1');
		});
		$("#eleve-accordR2").click(function() {
			var action = ($(this).is(':checked')) ? 'check' : 'uncheck';
			var subvention = $("#eleve-subventionR2").is(':checked');
			$.ajax({
				url : '/sbmajaxeleve/' + action + 'accordR2/eleveId:' + ELEVE_ID,
				success : function(data) {
					montreMotifRefus(action == 'check', subvention, 'r2');
					montreBlockAffectation(action == 'check', 'r2');
				},
				error : function(xhr, ajaxOptions, thrownError) {
					alert(xhr.status + ' ' + thrownError);
				}
			});
		});
		$("#eleve-subventionR2").click(function() {
			var accord = $("#eleve-accordR2").is(':checked');
			var subvention = $(this).is(':checked');
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
			var args = 'etablissementId:' + etablissementid + '/responsableId:' + responsableid;
			$.ajax({
				url : '/sbmajaxeleve/donnedistance/' + args,
				type : 'GET',
				dataType : 'json',
				success : function(data) {
					$(myid).val(data.distance);
					$(myid).css('cursor', 'auto');
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
				url : '/sbmajaxeleve/getresponsable/responsableId:' + responsableid,
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
				url : '/sbmajaxeleve/getresponsable/responsableId:'+ responsableid,
				success : function(data) {
					montreResponsable('r2', data);
				},
				error : function(xhr, ajaxOptions, thrownError) {
					alert(xhr.status + " " + thrownError);
				}
			});
		});
		$("#tabs").on('click', "i[data-button=btnaffectation]",function() {
			var trajet = $(this).attr(
			'data-trajet');
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
				// ,
				// position:'center'
			});
			$("#winpopup").load(href);
			$("#winpopup").dialog("open");

			return false;
		});
		$("#eleve-btnpaiement").click(function() {
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
		"init" : function(disableAccordR1, disableAccordR2, tarifs) {
			js_edit.disableAccordR1 = disableAccordR1;
			js_edit.disableAccordR2 = disableAccordR2;
			js_edit.tarifs = tarifs;
			$("#tabs").tabs();
			montreDebutFin($("#eleve-anneeComplete").is(":checked"));
			majMontantInscription($("#eleve-anneeComplete").is(":checked"));
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