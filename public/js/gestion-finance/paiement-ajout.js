/**
 * Ensemble des scripts des pages de sbm-gestion/finance pour les paiements :
 * paiement-ajout.phtml, paiement-detail.phtml
 * 
 * @project sbm
 * @filesource paiement-ajout.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 fév. 2019
 * @version 2019-2.5.0
 */

/**
 * Cet objet met en place les listeners 
 * et propose la méthode publique init().
 */
var js_paiement_ajout = (function(){
	var montantDuplicatas;
	var nbPreinscrits;
	/**
	 * méthode privée chargeListe() faisant un appel ajax pour mettre à jour :
	 * - montantDuplicatas
	 * - nbPreinscrits
	 * Elle modifie le document html en remplaçant le tableau des preinscrits et le montant du.
	 * Elle appelle la méthode privée echoSommeDue().
	 */
	function chargeListe() {
		var valeur = $("#responsableId").val() | 0;
		$.ajax({
			url : '/sbmajaxfinance/listepreinscrits/responsableId:'+valeur,
			dataType: 'json',
			success : function(dataJson){
				var montantDu = parseFloat(montantDuplicatas);			        			        
				$("#tbody-preinscrits").empty();
				$("#tbody-preinscrits").append('<tr><th>Nom</th><th>Prénom</th><th>Montant</th><th></th></tr>');
				if (dataJson.success) {
			    	montantDuplicatas = dataJson.duplicatas;
			    	nbPreinscrits = 0;
			    	$.each(dataJson.data, function(k, d) {
			        	nbPreinscrits++;
			        	var nom = d['nom'];
			        	var prenom = d['prenom'];
			        	var montant = d['montant'];
			        	montantDu += parseFloat(montant);
			        	var checkbox = '<input type="checkbox" name="eleveId[]" value="'+d['eleveId']+'" checked>';
			            $('#tbody-preinscrits').append("<tr><td>"+nom+"</td><td>"+prenom+"</td><td>"+montant+"</td><td>"+checkbox+"</td></tr>");
			        });
			    }
			    echoSommeDue(montantDu);
			    $("#paiement-montant").val(parseFloat(montantDu).toFixed(2));
			},
			error : function(xhr, ajaxOptions, thrownError) {
				alert(xhr.status + " " + thrownError);
			}
		});
	}
	/**
	 * méthode privée totalAPayer() qui renvoit le total des sommes dues inscrites dans le tableau.
	 * Elle appelle la méthode privée echoSommeDue().
	 * 
	 * @return float
	 */
	function totalAPayer() {
		var total = montantDuplicatas;
		$("#tbody-preinscrits > tr:not(:first)").each(function(i) {
			var element = $("td:nth-child(4) > input[type=checkbox]")[i];
			if ($(element).is(':checked')) {
		    	var cellMontant = $(element).parent().parent().find("td:nth-child(3)")[0];
		    	total=total+parseFloat($(cellMontant).text());
			}
		});
		echoSommeDue(total);
		return total;  
	}
	/**
	 * méthode privée echoSommeDue() qui met à jour la <div id="somme-due"> dans le document.
	 * 
	 * @param float montant
	 */
	function echoSommeDue(montant) {
		var texte = '<p class="left-10px">Somme due : '+parseFloat(montant).toFixed(2)+' €</p>';
		$("#somme-due").empty();
		$("#somme-due").append(texte);
	}
	// mise en place des listeners
	$(document).ready(function() {
		//listener sur le select #responsableId
		$("#responsableId").change(function() {
			chargeListe();
		}).trigger('change');
		// listener sur les cases à cocher du tableau des préinscrits
		$("#tbody-preinscrits").on('click', "input[type=checkbox]", function() {
		      $("#paiement-montant").val(parseFloat(totalAPayer()).toFixed(2));
		});
		// listener sur le submit du formulaire paiement
		$("#paiement").submit(function(){
			var btn = $(document.activeElement);
			if ($(btn).length && $(btn).is("[name]") && $(btn).attr("name")=="cancel") {
		    	return true;
			}
			var sommeDue=totalAPayer();
			var montant=$("#paiement-montant").val() | 0;
			if (nbPreinscrits>0 && montant>0 && sommeDue==0) {
		    	return confirm("Vous n'avez pas coché d'enfant ; ce paiement ne validera pas les préinscriptions. Confirmez-vous ?");
			} 
			if (montant!=sommeDue) {
		    	return confirm("Le montant indiqué ne correspond pas à la somme due. Confirmez-vous ?");
			}
			return true;
		});
	});
	// méthodes publiques
	return {
		"init": function(){
			montantDuplicatas = 0.0;
			nbPreinscrits = 0;
			chargeListe();
		}
	}
})();

var js_paiement_detail = {
		
};