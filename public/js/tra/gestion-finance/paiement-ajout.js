/**
 * Ensemble des scripts des pages de sbm-gestion/finance pour les paiements :
 * paiement-ajout.phtml, paiement-detail.phtml
 * 
 * @project sbm
 * @filesource paiement-ajout.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 avr. 2019
 * @version 2019-2.5.0
 */

/**
 * Cet objet met en place les listeners 
 * et propose la méthode publique init().
 */
var js_paiement_ajout = (function(){
	var sommeDue;
	var sommePayee;
	var nbPreinscrits;
	/**
	 * méthode privée chargeListe() faisant un appel ajax pour mettre à jour 
	 * - la liste des enfants
	 * - le montant du
	 * - le montant proposé dans le formulaire
	 * Elle modifie le document html en remplaçant le tableau des preinscrits et le montant du.
	 * Elle appelle la méthode privée totalAPayer().
	 */
	function chargeListe() {
		var valeur = $("#responsableId").val() | 0;
		$.ajax({
			url : '/sbmajaxfinance/listepreinscrits/responsableId:'+valeur,
			dataType: 'json',
			success : function(dataJson){		        			        
				$("#tbody-preinscrits").empty();
				$("#tbody-preinscrits").append('<tr><th>Nom</th><th>Prénom</th><th colspan="2">Tarif</th><th>Duplicata</th><th></th></tr>');
				if (dataJson.success) {
			    	nbPreinscrits = 0;
			    	$.each(dataJson.data, function(k, d) {
			        	nbPreinscrits++;
			        	var nom = d['nom'];
			        	var prenom = d['prenom'];
			        	var tarif = d['grilleTarif'];
			        	var reduit = d['reduction'];
			        	var duplicata = d['duplicata'];
			        	var checkbox = '<input type="checkbox" name="eleveId[]" value="'+d['eleveId']+'" checked>';
			            $('#tbody-preinscrits').append("<tr><td>"+nom+"</td><td>"+prenom+"</td><td>"+tarif+"</td><td>"+reduit+"</td><td class=\"align-right\">"+duplicata+"</td><td>"+checkbox+"</td></tr>");
			        });
			    }
				totalAPayer();
			},
			error : function(xhr, ajaxOptions, thrownError) {
				alert(xhr.status + " " + thrownError);
			}
		});
	}
	/**
	 * méthode privée totalAPayer() qui demande le total des sommes dues pour les élèves cochés dans le tableau.
	 * Elle appelle la méthode privée echoSommeDue() et elle met à jour la somme proposée dans le formulaire.
	 * 
	 * @return float
	 */
	function totalAPayer() {
		var result = [];
		$("#tbody-preinscrits > tr:not(:first)").each(function(i) {			
			var element = $("td:nth-child(6) > input[type=checkbox]")[i];
			if ($(element).is(':checked')) {
				result.push(element.value);
			}
		});
		var responsableId = $("#responsableId").val() | 0;
		var eleveIds = encodeURIComponent(JSON.stringify(result));
		var urlstr = '/sbmajaxfinance/calculmontant/responsableId:'+responsableId+'/eleveIds:'+eleveIds;
		$.ajax({
			url: urlstr,
			dataType: 'json',
			success: function(dataJson) {
				sommeDue = dataJson.total;
				sommePayee = dataJson.paye;
				var solde = dataJson.solde;
				if (dataJson.success == 0) {
					alert('Le responsable est inconnu.');
				}
				echoSommeDue();
			    $("#paiement-montant").val(parseFloat(solde).toFixed(2));
			},
			error : function(xhr, ajaxOptions, thrownError) {
				alert(xhr.status + " " + thrownError);
			}
		});
	}
	/**
	 * méthode privée echoSommeDue() qui met à jour la <div id="somme-due"> dans le document.
	 * 
	 * @param float montant
	 */
	function echoSommeDue() {
		var texte = '<span class="left-10px">Somme due : '+parseFloat(sommeDue).toFixed(2)+' €</span	>';
		$("#somme-due").empty();
		$("#somme-due").append(texte);
		texte = '<span class="left-10px">Somme payée : '+parseFloat(sommePayee).toFixed(2)+' €</span	>';
		$("#somme-payee").empty();
		$("#somme-payee").append(texte);
	}
	// mise en place des listeners
	$(document).ready(function() {
		//listener sur le select #responsableId
		$("#responsableId").change(function() {
			chargeListe();
		}).trigger('change');
		// listener sur les cases à cocher du tableau des préinscrits
		$("#tbody-preinscrits").on('change', "input[type=checkbox]", function() {
		      $("#paiement-montant").val(parseFloat(totalAPayer()).toFixed(2));
		});
		// listener sur le submit du formulaire paiement
		$("#paiement").submit(function(){
			var btn = $(document.activeElement);
			if ($(btn).length && $(btn).is("[name]") && $(btn).attr("name")=="cancel") {
		    	return true;
			}
			var montant=parseFloat($("#paiement-montant").val());
			if (isNaN(montant)) {
				montant = 0.0;
			}
			if (nbPreinscrits>0 && montant>0 && sommeDue==0) {
		    	return confirm("Vous n'avez pas coché d'enfant ; ce paiement ne validera pas les préinscriptions. Confirmez-vous ?");
			} 
			var solde = sommeDue-sommePayee;
			if (montant!=solde) {
		    	return confirm("Le montant indiqué ("+
		    			parseFloat(montant).toFixed(2)+
		    			") ne correspond pas à la somme due ("+
		    			parseFloat(solde).toFixed(2)+
		    			"). Confirmez-vous ?");
			}
			return true;
		});
	});
	// méthodes publiques
	return {
		"init": function(){
			nbPreinscrits = 0;
			chargeListe();
		}
	}
})();

var js_paiement_detail = {
		
};