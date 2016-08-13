/**
 * Ensemble des scripts des pages de sbm-gestion/eleve pour les élèves :
 * eleve-ajout.phtml, eleve-ajout31.phtml, eleve-edit.phtml
 * 
 * @project sbm
 * @filesource gestion-eleve.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 août 2016
 * @version 2016-2.1.10
 */
var js_ajout = {
	"montreResponsable2" : function(voir) {
		if (voir) {
			$("#wrapper-responsable2Id").show();
		} else {
			$("#wrapper-responsable2Id").hide();
		}
	}
};

var js_edit = {
	"initAccord" : function(disableAccordR1, disableAccordR2) {
		if (disableAccordR1) {
			$("#eleve-accordR1").attr("disabled", true);
			$("input[name=accordR1][type=hidden]").val(1);
		} else {
			$("#eleve-accordR1").removeAttr("disabled");
			$("input[name=accordR1][type=hidden]").val(0);
		}
		;
		if (disableAccordR2) {
			$("#eleve-accordR2").attr("disabled", true);
			$("input[name=accordR2][type=hidden]").val(1);
		} else {
			$("#eleve-accordR2").removeAttr("disabled");
			$("input[name=accordR2][type=hidden]").val(0);
		}
	},
	"montreDebutFin" : function(ancomplet) {
		if (ancomplet) {
			$("#wrapper-dateDebut").hide();
			$("#wrapper-dateFin").hide();
		} else {
			$("#wrapper-dateDebut").show();
			$("#wrapper-dateFin").show();
		}
	},
	"montreMotifDerogation" : function(derogation) {
		if (derogation) {
			$("#wrapper-motifDerogation").show();
		} else {
			$("#wrapper-motifDerogation").hide();
		}
	},
	"montreOngletGa" : function(voir) {
		if (voir) {
			$("#tabs li").eq(1).show();
		} else {
			$("#tabs li").eq(1).hide();
		}
	},
	// pour les fonctions suivantes r prend la valeur 'r1' ou 'r2'
	"montreDemande" : function(r) {
		if ($("#demande" + r + "radio0").is(":checked")) {
			$("#block-demande" + r).hide();
			$("#block-affectations" + r).hide();
		} else {
			$("#block-demande" + r).show();
			$("#block-affectations" + r).show();
		}
	},
	"montreMotifRefus" : function(accord, subvention, r) {
		if (accord) {
			if (subvention) {
				$("#wrapper-motifRefus" + r).show();
			} else {
				$("#wrapper-motifRefus" + r).hide();
			}
		} else {
			$("#wrapper-motifRefus" + r).show();
		}
	},
	"montreBlockAffectation" : function(accord, r) {
		if (accord) {
			$("#block-affectations" + r).show();
		} else {
			$("#block-affectations" + r).hide();
		}
	},
	"montreResponsable" : function(r, data) {
		var oresponsable = $.parseJSON(data);
		var part_html;
		var responsable;
		responsable = oresponsable.titre + ' ' + oresponsable.nom + ' '
				+ oresponsable.prenom;
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
	},
	"majBlockAffectations" : function(trajet) {
		$('html', 'body').css('cursor', 'auto');
		var args1 = 'eleveId:' + ELEVE_ID + '/identite:' + IDENTITE
				+ '/trajet:' + trajet;
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
			error : function(xhr, ajaxOptions, throxnError) {
				alert(xhr.status + " " + thrownError);
			}
		});
	}
};