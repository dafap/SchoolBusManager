/**
 * scripts des pages de configuration de l'application
 * 
 * @project sbm
 * @filesource gestion-config.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 avr. 2019
 * @version 2019-2.5.0
 */

var js_edit_arrayN2Idx = (function() {
	// fonctions privées
	function ajoutLigne(nom) {
		var idmax = $('input[type=text][name^="'+nom+'"]').length;
		var id = nom + (idmax + 1);
		var txt = '<br><label for="' + id + '">'+nom+'[]</label> ' + 
				  '<input type="text" name="'+nom+'[]" id="'+id+'">';
		$('#'+nom+idmax).after(txt);
	}
	function creerBouton(nom, libelle) {
		var txt = '<input type="button" name="'+nom+'" value="'+libelle+'">';
		$('input[name="cancel"]').after(txt);
	}
	// mise en place des listeners
	// selector pour filtrer les descendants du selector
	// data pour passer à la fonction dans l'event e sous e.data
	$(document).ready(function() {
		$("input[type=button]").on('click', function(e) {
			var nom = $(this).attr('name');
			ajoutLigne(nom);
		});
	});
	// méthodes publiques
	// (dans ces méthodes on passe tous les paramètres qu'on veut)
	return {
		"init" : function(libelle, elements) {
			for (var i = 0; i < elements.length; i++) {
				creerBouton(elements[i], libelle + elements[i]);
			}
		}
	}
})();

var js_edit_arrayN2Asso = (function() {
	// variables de la classe
	var x;
	// fonctions privées
	function ajoutLigne(nom) {
		var idmax = $('input[type=text][name^="index-'+nom+'"]').length;
		var id = nom + (idmax + 1);
		var txt = '<br><label for="index-'+id+'">' + nom + '[]</label> ' + 
				  '<input type="text" name="index-'+nom+'[]" id="index-'+id+'"> ' +
				  ' <code>=></code> ' +
				  '<input type="text" name="value-'+nom+'[]" id="value-'+id+'">';
		$('#value-'+nom+idmax).after(txt);
	}
	function creerBouton(nom, libelle) {
		var txt = '<input type="button" name="'+nom+'" value="'+libelle+'">';
		$('input[name="cancel"]').after(txt);
	}
	// mise en place des listeners
	// selector pour filtrer les descendants du selector
	// data pour passer à la fonction dans l'event e sous e.data
	$(document).ready(function() {
		$("input[type=button]").on('click', function(e) {
			var nom = $(this).attr('name');
			ajoutLigne(nom);
		});
	});
	// méthodes publiques
	// (dans ces méthodes on passe tous les paramètres qu'on veut)
	return {
		"init" : function(libelle, elements) {
			for (var i = 0; i < elements.length; i++) {
				creerBouton(elements[i], libelle + elements[i]);
			}
		}
	}
})();