/**
 * Ensemble des scripts des pages de sbm-gestion/eleve/eleve-edit.phtml
 * 
 * @project sbm
 * @filesource edit.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 juil. 2020
 * @version 2020-2.6.0
 */

$(function(){
	var r = $('input[type="radio"].input-error').parent().parent().parent();
	r.addClass('input-error');
	var c = $('input[type="checkbox"].input-error').parent().parent().parent();
	c.addClass('input-error');
});

var js_edit = (function() {
	var disableAccordR1;
	var disableAccordR2;
	var subventionR1;
	var subventionR2;
	var hasPhoto;
	function majAbonnement() {
	}
	function estTropPres() {
		return ($("#eleve-distanceR1").val() < 1) && ($("#eleve-distanceR2").val() < 1);
	}
	function estHorsDistrict() {
		return !$("#eleve-district").is(":checked");
	}
	function initAccord() {
		$("input[name=subventionR1][type=hidden]").val(subventionR1);
		$("input[name=subventionR2][type=hidden]").val(subventionR2);
		if ($("#demander1radio1").is(":checked")) {
			$("#eleve-subventionR1").removeAttr("disabled");
			if (disableAccordR1) {
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
			if (disableAccordR2) {
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
	function setAccordRi(i, accord) {
		$("input[name=accordR"+i+"][type=hidden]").val(Number(accord));
	}
	function setDisableAccordRi(i, disable) {
		if (i == 1) {
			disableAccordR1 = disable;
			if (disable) {
				$("#eleve-accordR1").attr("checked", true);
				setAccordRi(1, true);
			}
		} else {
			disableAccordR2 = disable;
			if (disable) {
				$("#eleve-accordR2").attr("ckecked", true);
				setAccordRi(2, true);
			}
		}
	}
	function setSubventionRi(i,subvention) {
		subvention = Number(subvention);
		if (i == 1) {
			subventionR1 = subvention;
		} else {
			subventionR2 = subvention;
		}		
		$("input[name=subventionR"+i+"][type=hidden]").val(subvention);
	}
	function setHasPhoto(ouinon) {
		hasPhoto = ouinon;
		if (hasPhoto) {
			$("button[name=supprphoto]").show();
			$("#btnoutilsphoto").show();
		} else {
			$("button[name=supprphoto]").hide();
			$("#btnoutilsphoto").hide();
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
	function majGrilleTarifaire() {
		$("#tabs-3-grilleTarif").empty();
	}
	function montreDerogation() {
		if (estTropPres() || estHorsDistrict()) {
			$("#commun-col2-derogation").show();
		} else {
			$("#eleve-derogation").val(0);
			$("#commun-col2-derogation").hide();
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
	function montreDemande(r) {
		if ($("#demande"+r+"radio0").is(":checked")) {
			$("#block-demande"+r).hide();
		} else {
			$("#block-demande"+r).show();
		}
		var accordElementName = "#eleve-accord"+r.toUpperCase();
		if ($("#demande"+r+"radio1").is(":checked")) {
			var accord = $(accordElementName).is(':checked');
			var disableAccord = r == 'r1' ? disableAccordR1 : disableAccordR2;
			if (disableAccord) {
				$(accordElementName).attr("disabled", true);
			} else {
				$(accordElementName).removeAttr("disabled");
			}
			$("#eleve-subvention"+r.toUpperCase()).removeAttr("disabled");
			montreBlockAffectation(accord, r);
		} else {
			if (r.toUpperCase() == 'R1') {
				setAccordRi(1, $(accordElementName).is(':checked'));
			} else {
				setAccordRi(2, $(accordElementName).is(':checked'));
			}
			$(accordElementName).attr("disabled", true);
			$("#eleve-subvention"+r.toUpperCase()).attr("disabled", true);
			$("#block-affectations"+r+" i").hide();
		}
	}
	function montreMotifRefus(accord, subvention, r) {
		if (accord) {
			if (subvention) {
				$("#wrapper-motifRefus"+r).show();
			} else {
				$("#wrapper-motifRefus"+r).hide();
			}
		} else {
			$("#wrapper-motifRefus"+r).show();
		}
	}
	function montreBlockAffectation(accord, r) {
		if (accord) {
			$("#block-affectations"+r+" i").show();
		} else {
			$("#block-affectations"+r+" i").hide();
		}
	}
	function montrePhoto(data, success) {
		$("input[type=file][name=filephoto]").val('');
		montreBtnEnvoiPhoto(false);
		$("#wrapper-filephoto ul").remove();
		if (success == 1) {
			$("#wrapper-photo img").attr('src', data);
		} else {
			$("#wrapper-filephoto").append('<ul><li>'+data+'</li></ul>');
		}
	}
	function montreResponsable(r, data) {
		var oresponsable = $.parseJSON(data);
		var part_html;
		var responsable;
		responsable = oresponsable.titre+' '+oresponsable.nom+' '+oresponsable.prenom;
		if (oresponsable.nom2.trim() != '') {
			responsable += ' ou '+oresponsable.titre2+' '+oresponsable.nom2+' '+oresponsable.prenom2;
		}
		$("#"+r+"-ligne1").html(responsable);
		$("#"+r+"-ligne2").html(
				oresponsable.adresseL1+' '+oresponsable.adresseL2+' '+oresponsable.adresseL3);
		$("#"+r+"-ligne3").html(oresponsable.codePostal+' '+oresponsable.commune);
		if (!!oresponsable.email) {
			part_html = [];
			part_html.push('email: '+oresponsable.email);
			part_html.push('<div id="email'+r+'" style="display: inline; margin-left: 5px;">');
			part_html.push('<input type="hidden" name="email'+r+'" value="'+oresponsable.email+'">');
			part_html.push('<input type="hidden" name="responsable'+r+'" value="'+responsable+'">');
			part_html.push('<input type="hidden" name="group" value="'+URL_ICI+'">');
			part_html.push('<input type="submit" name="ecrire'+r
					+'" class="fam-email" title="Envoyer un email" value="" formaction="/gestion/eleve/responsable-mail">');
			part_html.push('</div>');
			$("#"+r+"-ligne4").html(part_html.join('\n'));
		} else {
			$("#"+r+"-ligne4").empty();
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
		$("#"+r+"-ligne5").html(part_html.join(' '));
		if (r=='r1') {
			$("#wrapper-responsable1").removeClass('attendre');
		} else {
			$("#wrapper-responsable2").removeClass('attendre');
		}
	}
	$(document).ready(function($) {
		var lastEtablissementSel = $("#eleve-etablissementId option:selected");
		$("#eleve-etablissementId").click(function(){
			lastEtablissementSel = $("#eleve-etablissementId option:selected");
		});
		// adapte le select de la classe à l'établissement
		$("#eleve-etablissementId").change(function() {
			if (disableAccordR1 || disableAccordR2) {
				lastEtablissementSel.prop("selected", true);
				alert("Vous devez supprimer d'abord les affectations avant de changer l'établissement.");
				return;
			}
			var classeId = $("#eleve-classeId").val();
			var etablissementId = $(this).val();
			$.ajax({
				url : '/sbmajaxeleve/getclassesforselect/etablissementId:'+etablissementId,
				dataType : 'json',
				success : function(dataJson) {
					if (dataJson.success == 1) {
						var select = $("#eleve-classeId");
						select.empty();
						$.each(dataJson.data,function(niveau,descripteur) {
							var optgroup = $("<optgroup></optgroup>");
							optgroup.attr('label',descripteur.label);
							$.each(descripteur.options,function(id,libelle) {
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
				error : function(xhr,ajaxOptions,thrownError) {
					alert(xhr.status+" "+thrownError);
				}
			});
		});
		$("button[data-button=duplicatamoins]").click(function() {
			var trajet = $(this).attr('data-trajet');
			$.ajax({
				url : '/sbmajaxeleve/decrementeduplicata/eleveId:'+ELEVE_ID+'/trajet:'+trajet,
				dataType : 'json',
				success : function(data) {
					var propriete = 'duplicataR' + trajet
					var myid = "#nb" + propriete;
					var duplicata = data[propriete];
					$(myid).empty();
					$(myid).append(duplicata.toString());
				},
				error : function(xhr,ajaxOptions,thrownError) {
					alert(xhr.status+' '+thrownError);
				}
			});
		});
		$("button[data-button=duplicataplus]").click(function() {
			var trajet = $(this).attr('data-trajet');
			$.ajax({
				url : '/sbmajaxeleve/incrementeduplicata/eleveId:'+ELEVE_ID+'/trajet:'+trajet,
				dataType : 'json',
				success : function(data) {
					var propriete = 'duplicataR' + trajet
					var myid = "#nb" + propriete;
					var duplicata = data[propriete];
					$(myid).empty();
					$(myid).append(duplicata.toString());
				},
				error : function(xhr,ajaxOptions,thrownError) {
					alert(xhr.status+' '+thrownError);
				}
			});
		});
		$("input[name=regimeId]").change(function() {
			majGrilleTarifaire();
		});
		$("#eleve-anneeComplete").click(function() {
			montreDebutFin($(this).is(":checked"));
			majAbonnement();
		});
		$("#eleve-derogation").change(function() {
			montreMotifDerogation($(this).find("option:selected").val() > 0);
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
				url : '/sbmajaxeleve/'+action+'accordR1/eleveId:'+ELEVE_ID,
				success : function(data) {
					montreMotifRefus(action == 'check',subvention,'r1');
					montreBlockAffectation(action == 'check','r1');
				},
				error : function(xhr,ajaxOptions,thrownError) {
					alert(xhr.status+' '+thrownError);
				}
			});
		});
		$("#eleve-subventionR1").click(function() {
			var accord = $("#eleve-accordR1").is(':checked');
			var subvention = $(this).is(':checked');
			setSubventionRi(1, subvention);
			montreMotifRefus(accord, subvention, 'r1');
		});
		$("#eleve-accordR2").click(function() {
			var action = ($(this).is(':checked')) ? 'check' : 'uncheck';
			var subvention = $("#eleve-subventionR2").is(':checked');
			$.ajax({
				url : '/sbmajaxeleve/'+action+'accordR2/eleveId:'+ELEVE_ID,
				success : function(data) {
					montreMotifRefus(action == 'check',subvention,'r2');
					montreBlockAffectation(action == 'check','r2');
				},
				error : function(xhr,ajaxOptions,thrownError) {
					alert(xhr.status+' '+thrownError);
				}
			});
		});
		$("#eleve-subventionR2").click(function() {
			var accord = $("#eleve-accordR2").is(':checked');
			var subvention = $(this).is(':checked');
			setSubventionRi(2, subvention);
			montreMotifRefus(accord, subvention, 'r2');
		});
		$("input[type='text'][name^='distanceR']").dblclick(function() {
			$(this).css("cursor", "wait");
			var myid = '#'+$(this).attr('id');
			var name = $(this).attr('name');
			var n = 1;
			if (name.indexOf('1') == -1) {
				n = 2;
			}
			var id = '#eleve-responsable'+n+'Id';
			var responsableid = $(id).val();
			var etablissementid = $("#eleve-etablissementId").val();
			var args = 'etablissementId:'+etablissementid+'/responsableId:'+responsableid;
			$.ajax({
				url : '/sbmajaxeleve/donnedistance/'+args,
				type : 'GET',
				dataType : 'json',
				success : function(data) {
					$(myid).val(data.distance);
					$(myid).css('cursor','auto');
					montreDerogation();
				},
				error : function(xhr,ajaxOptions,thrownError) {
					alert(xhr.status+" "+thrownError);
					$(myid).css('cursor','auto');
				}
			});
		});
		$("input[type='text'][name^='distanceR']").blur(function() {
			montreDerogation();
		});
		$("#eleve-responsable1Id").change(function() {
			var responsableid = $(this).val();
			$("#wrapper-responsable1").addClass("attendre");
			$.ajax({
				url : '/sbmajaxeleve/getresponsable/responsableId:'+responsableid,
				success : function(data) {
					montreResponsable('r1', data);
				},
				error : function(xhr,ajaxOptions,thrownError) {
					alert(xhr.status+" "+thrownError);
				}
			});
		});
		$("#eleve-responsable2Id").change(function() {
			var responsableid = $(this).val();
			$("#wrapper-responsable1").addClass("attendre");
			$.ajax({
				url : '/sbmajaxeleve/getresponsable/responsableId:'+ responsableid,
				success : function(data) {
					montreResponsable('r2', data);
				},
				error : function(xhr,ajaxOptions,thrownError) {
					alert(xhr.status+" "+thrownError);
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
			progressbar.css('width', '0');
			$.ajax({
				xhr : function() {
					var xhr = new window.XMLHttpRequest();
					xhr.upload.addEventListener("progress",function(evt) {
						if (evt.lengthComputable) {
							var percentComplete = parseInt(100 * evt.loaded / evt.total);
							progressbar.css('width',percentComplete+'%');
							progressbar.text(percentComplete+'%');
						}
					},false);
					xhr.addEventListener("progress",function(evt) {
						if (evt.lengthComputable) {
							var percentComplete = evt.loaded / evt.total;
							console.log(percentComplete);
						}
					},false);
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
						montrePhoto(retour.src,true);
						setHasPhoto(true);
					} else {
						montrePhoto(retour.cr,false);
					}
					$("#photo-form-modele").dialog('close');
					containerprogress.hide();
				},
				error : function(xhr,ajaxOptions,thrownError) {
					alert(xhr.status+" "+thrownError);
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
				error : function(xhr,ajaxOptions,thrownError) {
					alert(xhr.status+" "+thrownError);
				}
			});
		});
		$("button[type=button][name=quartgauchephoto]").click(function() {
			var eleveid = $("input[type=hidden][name=eleveId]").val();
			var fd = new FormData();
			fd.append('eleveId', eleveid);
			$.ajax({
				url : '/sbmajaxeleve/quartgauchephoto',
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
				},
				error : function(xhr,ajaxOptions,thrownError) {
					alert(xhr.status+" "+thrownError);
				}
			});
		});
		$("button[type=button][name=quartdroitephoto]").click(function() {
			var eleveid = $("input[type=hidden][name=eleveId]").val();
			var fd = new FormData();
			fd.append('eleveId', eleveid);
			$.ajax({
				url : '/sbmajaxeleve/quartdroitephoto',
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
				},
				error : function(xhr,ajaxOptions,thrownError) {
					alert(xhr.status+" "+thrownError);
				}
			});
		});
		$("button[type=button][name=retournephoto]").click(function() {
			var eleveid = $("input[type=hidden][name=eleveId]").val();
			var fd = new FormData();
			fd.append('eleveId', eleveid);
			$.ajax({
				url : '/sbmajaxeleve/retournephoto',
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
				},
				error : function(xhr,ajaxOptions,thrownError) {
					alert(xhr.status+" "+thrownError);
				}
			});
		});
		$("#tabs").on('click',"i[data-button=btnaffectation]",function() {
			var etablissementId = $("#eleve-etablissementId").val();
			var trajet = $(this).attr('data-trajet');
			var respid = '#eleve-responsable'+trajet+'Id';
			var href = '/sbmajaxeleve/formaffectation/etablissementId:'
				+ etablissementId
				+ '/eleveId:'
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
		$("#tabs").on('click',"i[data-button=btnchercheraffectations]",function() {
			var etablissementId = $("#eleve-etablissementId").val();
			var trajet = $(this).attr('data-trajet');
			var respid = '#eleve-responsable'+trajet+'Id';
			var stationid = '#stationIdR' + trajet;
			var href = '/sbmajaxeleve/formchercheraffectations/etablissementId:'
				+ etablissementId
				+ '/eleveId:'
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
		$("#eleve-btnpaiement").click(function() {
			var href = '/sbmajaxeleve/formpaiement/eleveId:'+ELEVE_ID;
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
		"init" : function(disableAccordR1, disableAccordR2, subventionR1,subventionR2, hasPhoto) {
			setHasPhoto(hasPhoto);
			setDisableAccordRi(1, disableAccordR1);
			setDisableAccordRi(2, disableAccordR2);
			setSubventionRi(1,subventionR1);
			setSubventionRi(2,subventionR2);
			$("#tabs").tabs();
			montreDebutFin($("#eleve-anneeComplete").is(":checked"));
			majAbonnement();
			montreDerogation();
			montreMotifDerogation($("#eleve-derogation").find("option:selected").val() > 0);
			montreOngletGa($("#eleve-ga").is(":checked"));
			montreMotifRefus($("#eleve-accordR1").is(":checked"), $("#eleve-subventionR1").is(":checked"), 'r1');
			montreMotifRefus($("#eleve-accordR2").is(":checked"), $("#eleve-subventionR2").is(":checked"), 'r2');
			montreDemande('r1');
			montreDemande('r2');
			montreBtnEnvoiPhoto(false);
			initAccord();
			$("#wrapper-responsable1").addClass("attendre");
			$.ajax({
				url : '/sbmajaxeleve/getresponsable/responsableId:'+$("#eleve-responsable1Id").val(),
				success : function(data) {
					montreResponsable('r1', data);
				},
				error : function(xhr, ajaxOptions, thrownError) {
					alert(xhr.status+" "+thrownError);
				}
			});
			if ($("#eleve-responsable2Id").val()) {
				$("#wrapper-responsable2").addClass("attendre");
				$.ajax({
					url : '/sbmajaxeleve/getresponsable/responsableId:'+$("#eleve-responsable2Id").val(),
					success : function(data) {
						montreResponsable('r2', data);
					},
					error : function(xhr, ajaxOptions, thrownError) {
						alert(xhr.status+" "+thrownError);
					}
				});
				montreDemande('r1');
				montreDemande('r2');
			}
		},
		"majDisableAccordRi" : function(trajet, nbaffectations){
			if (trajet == 1) {
				disableAccordR1 = nbaffectations > 0;
			} else {
				disableAccordR2 = nbaffectations > 0;
			}
		},
		"majBlockAffectations" : function(trajet) {
			$('html', 'body').css('cursor', 'auto');
			var args1 = 'eleveId:'+ELEVE_ID+'/identite:'+IDENTITE+'/trajet:'+trajet;
			$.ajax({
				url : '/sbmajaxeleve/blockaffectations/'+args1,
				type : 'GET',
				dataType : 'html',
				success : function(data) {
					var myid = '#block-affectationsr'+trajet;
					$(myid).empty();
					$(myid).append(data);
					$('html', 'body').css('cursor', 'auto');
				},
				error : function(xhr, ajaxOptions, thrownError) {
					alert(xhr.status+" "+thrownError);
					$('html', 'body').css('cursor', 'auto');
				}
			});
			var args2 = 'eleveId:'+ELEVE_ID+'/trajet:'+trajet;
			var args3 = "input[name=accordR"+trajet+"][type=hidden]";
			$.ajax({
				url : '/sbmajaxeleve/enableaccordbutton/'+args2,
				dataType : 'json',
				success : function(dataJson) {
					var myid = "#eleve-accordR"+trajet;
					if (dataJson.enable) {
						$(myid).removeAttr('disabled');
						$(args3).val(0);
					} else {
						$(myid).attr('disabled', 'disabled');
						$(args3).val(1);
					}
				},
				error : function(xhr, ajaxOptions, thrownError) {
					alert(xhr.status+" "+thrownError);
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

/**
 * POUR LE DIALOG `formaffectation`
 */
function affectation() {
	var is_xmlhttprequest;
	var formaffectation;
	var urlform;
	var station1Id;
	var station2Id;
	var btnclick;
	function setStationsValueOptions(valeur, station1Id, station2Id) {
		$.ajax({
			url : '/sbmajaxeleve/getstationsforselect/serviceId:'+valeur,
			dataType : 'json',
			success : function(dataJson) {
				$("#affectation-station1Id").empty();
				$("#affectation-station2Id").empty();
				if (dataJson.success) {
					$.each(dataJson.data, function(d, k) { // key/value échangées pour le conserver le tri
						if (station1Id == k) {
							$('#affectation-station1Id').append('<option value="'+k+'" selected>'+d+'</option>');
						} else {
							$('#affectation-station1Id').append('<option value="'+k+'">'+d+'</option>');
						}
						if (station2Id == k) {
							$('#affectation-station2Id').append('<option value="'+k+'" selected>'+d+'</option>');
						} else {
							$('#affectation-station2Id').append('<option value="'+k+'">'+d+'</option>');
						}
					});
				}
			},
			error : function(xhr, ajaxOptions, thrownError) {
				alert(xhr.status+" "+thrownError);
			}
		});
	}
	function setPossibleJours(valeur) {
		$.ajax({
			url: '/sbmajaxeleve/getpossiblejours/serviceId:'+valeur,
			dataType: 'json',
			success: function(dataJson) {
				$.each(dataJson.data, function(idx, obj){
					var chkbx = ":checkbox[value="+obj.value+"]";
					var prop1 = "attributes";
					var prop2 = "onclick";		
					$(chkbx).removeAttr(prop2);			
					if (prop1 in obj) {
						if (prop2 in obj[prop1]) {
							$(chkbx).prop('checked', false);
							$(chkbx).attr(prop2, obj[prop1][prop2]);
						}
					} 
				});
			},
			'error': function(xhr, ajaxOptions, thrownError) {
				alert(xhr.status+" "+thrownError);
			}
		});
	}
	$('i[class="fam-help"').click(function() {
		$("#formaffectation-help").show();
	});
	$('#affectation-cancel').click(function() {
		btnclick = 'cancel';
	});
	$('#affectation-submit').click(function() {
		btnclick = 'submit';
	});
	$("#affectation-form").submit(function() {
		if (is_xmlhttprequest == 0) {
			return true;
		}
		var trajet = $(formaffectation+' input[name=trajet]').val();
		var demande = 'demandeR'+trajet;
		var jours = 0;
		$(formaffectation+' input[type=checkbox]')
		    .map(function(){if($(this).is(':checked')){jours += parseInt($(this).val());}});
		var data = {
				'csrf' : $(formaffectation+' input[name=csrf]').val(),
				'etablissementId' : $(formaffectation+' input[name=etablissementId]').val(),
				'eleveId' : $(formaffectation+' input[name=eleveId]').val(),
				'millesime' : $(formaffectation+' input[name=millesime]').val(),
				'trajet' : trajet,
				'moment' : $(formaffectation+' input[name=moment]').val(),
				'days' : $(formaffectation+' input[name=days]').val(),
				'jours' : jours,
				'sens' : $(formaffectation+' input[name=sens]').val(),
				'correspondance' : $(formaffectation+' input[name=correspondance]').val(),
				'responsableId' : $(formaffectation+' input[name=responsableId]').val(),
				'demandeR1' : $(formaffectation+' input[name=demandeR1]').val(),
				'demandeR2' : $(formaffectation+' input[name=demandeR2]').val(),
				'service1Id' : $(formaffectation+' select[name=service1Id]').val(),
				'service2Id' : $(formaffectation+' select[name=service2Id]').val(),
				'station1Id' : $(formaffectation+' select[name=station1Id]').val(),
				'station2Id' : $(formaffectation+' select[name=station2Id]').val(),
				'op' : $(formaffectation+' input[name=op]').val(),
				'submit' : btnclick
		};
		$("#wrapper-formchercheraffectations").addClass('attendre');
		$.post(urlform, data, function(itemJson) {
			$("#winpopup").dialog('close');
			js_edit.majBlockAffectations(trajet);
		}, 'json').done(function(data){js_edit.majDisableAccordRi(trajet,data.nb);});
		return false;
	});
	$("#affectation-service1Id").on('change', function() {
		var valeur = $(this).val();
		setStationsValueOptions(valeur, null, null);
		setPossibleJours(valeur);
	});
	return {
		"init" : function(station1Id, station2Id, x, f, url) {
			var valeur = $("#affectation-service1Id option:selected").val();
			if (valeur) {
				setStationsValueOptions(valeur, station1Id, station2Id);
				setPossibleJours(valeur);
			}
			is_xmlhttprequest = x;
			formaffectation = f;
			urlform = url;
			btnclick = '';
		}
	}
}

/**
 * POUR LE DIALOG `priseenchargepaiement` Les sélecteurs commençant par
 * "#formpaiement" sont des DIV Ceux commençant par "#priseenchargepaiement"
 * sont des éléments du formulaire (ou lui même)
 */
function priseenchargepaiement() {
	var is_xmlhttprequest;
	var urlform;
	var btnclick;
	$("#priseenchargepaiement-cancel").click(function() {
		btnclick = 'cancel';
	});
	$("#priseenchargepaiement-submit").click(function() {
		btnclick = 'submit';
	});
	$("#priseenchargepaiement-form").submit(function() {
		if (is_xmlhttprequest == 0) {
			return true;
		}
		var gratuit = $('#formpaiement input[name=gratuit]:checked').val();
		var organismeId = $('#formpaiement select[name=organismeId] option:selected').val();
		var data = {
				'csrf' : $('#formpaiement input[name=csrf]').val(),
				'eleveId' : $('#formpaiement input[name=eleveId]').val(),
				'gratuit' : gratuit,
				'organismeId' : organismeId,
				'submit' : btnclick
		};
		$.post(urlform, data, function(itemJson) {
			if (itemJson['success'] == 1) {
				$("#winpopup").dialog('close');
				if (btnclick == 'submit') {
					js_edit.majPaiement(gratuit);
				}
			} else {
				alert(itemJson['cr']);
			}
		}, 'json');
		return false;
	});
	$("#priseenchargepaiement-gratuitradio0").on('change', function() {
		$("#formpaiement-organismeId").hide();
	});
	$("#priseenchargepaiement-gratuitradio1").on('change', function() {
		$("#formpaiement-organismeId").hide();
	});
	$("#priseenchargepaiement-gratuitradio2").on('change', function() {
		$("#formpaiement-organismeId").show();
	});
	return {
		"init" : function(x, url) {
			if ($('#formpaiement input[name=gratuit]:checked').val() == 2) {
				$("#formpaiement-organismeId").show();
			}
			is_xmlhttprequest = x;
			urlform = url;
			btnclick = '';
		}
	}
}

/**
 * POUR LE DIALOG 'stationdepart'
 */
function stationdepart() {
	var is_xmlhttprequest;
	var formstationdepart;
	var urlform;
	var btnclick;
	$('i[class="fam-help"').click(function() {
		$("#formchercheraffectations-help").show();
	});
	$('#stationdepart-cancel').click(function() {
		btnclick = 'cancel';
	});
	$('#stationdepart-submit').click(function() {
		btnclick = 'submit';
	});
	$("#stationdepart-form").submit(function() {
		if (is_xmlhttprequest == 0) {
			return true;
		}
		var trajet = $(formstationdepart+' input[name=trajet]').val();
		var data = {
				'etablissementId' : $(formstationdepart+' input[name=etablissementId]').val(),
				'eleveId' : $(formstationdepart+' input[name=eleveId]').val(),
				'millesime' : $(formstationdepart+' input[name=millesime]').val(),
				'trajet' : trajet,
				'jours' : $(formstationdepart+' input[name=jours]').val(),
				'responsableId' : $(formstationdepart+' input[name=responsableId]').val(),
				'stationId' : $(formstationdepart+' select[name=stationId]').val(),
				'raz' : $(formstationdepart+' input[name=raz]').val(),
				'op' : $(formstationdepart+' input[name=op]').val(),
				'submit' : btnclick
		};
		$("#wrapper-formchercheraffectations").addClass('attendre');
		$.post(urlform, data, function(itemJson) {
			$("#winpopup").dialog('close');
			js_edit.majBlockAffectations(trajet);
		}, 'json').done(function(data){
			if (data.success == 1) {
				js_edit.majDisableAccordRi(trajet,data.nb);
			} else {
				alert('Une erreur s\'est produite pendant le traitement de la demande.');
			}
		});
		return false;
	});
	return {
		"init" : function(x, f, url) {
			is_xmlhttprequest = x;
			formstationdepart = f;
			urlform = url;
			btnclick = '';
		}
	}
}