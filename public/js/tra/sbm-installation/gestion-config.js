/**
 * scripts des pages de configuration de l'application
 * 
 * @project sbm
 * @filesource gestion-config.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 mai 2019
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

var js_edit_calendar = (function(){
	return {
		"init" : function() {
			;
		},
		"add" : function() {
			var bloc = '<fieldset class="sbm-page1">'+
			'<div id="ordinal-new">'+
			'<label><span class="label">ordinal</span><input type="text" name="ordinal[]" value=""></label>'+
			'</div><div id="nature-new">'+
			'<label><span class="label">nature</span><input type="text" name="nature[]" value=""></label>'+
			'</div><div id="rang-new">'+
			'<label><span class="label">rang</span><input type="text" name="rang[]" value=""></label>'+
			'</div><div id="libelle-new">'+
			'<label><span class="label">libelle</span><input type="text" name="libelle[]" value=""></label>'+
			'</div><div id="description-new">'+
			'<label><span class="label">description</span><input type="text" name="description[]" value=""></label>'+
			'</div><div id="exercice-new">'+
			'<label><span class="label">exercice</span><input type="text" name="exercice[]" value=""></label>'+
			'</div></fieldset>';
			$("#addnew").before(bloc);
		}
	}
})();

var js_edit_jquery =(function(){
	return {
		"init" : function() {
			;
		},
		"addcss" : function(key, id) {
			var bloc = '<label><span class="label">CSS</span><input type="text" name="'+key+'|css[]" value=""></label>';
			$("#addcssnew"+id).before(bloc);
		},
		"addjs" : function(key, id) {
			var bloc = '<label><span class="label">JS</span><input type="text" name="'+key+'|js[]" value=""></label>';
			$("#addjsnew"+id).before(bloc);
		},
		"addlibrary" : function(nomtmp) {
			var bloc = '<fieldset class="sbm-page1">'+
			'<label><span class="label">library</span>'+
			'<input type="text" name="'+nomtmp+'|library" value=""></label>'+
			'<label><span class="label">mode</span>'+
			'<select name="'+nomtmp+'|mode">'+
			'<option value="prepend">prepend</option>'+
			'<option value="append" selected="selected">append</option></select></label>'+
			'<div class="clearfix"><div class="float-left">'+
			'<label><span class="label">CSS</span><input type="text" name="'+nomtmp+'|css[]" value=""></label>'+
			'<div id="addcssnew'+nomtmp+'"></div></div>'+
			'<input type="button" class="float-right" value="Autre CSS" '+
			'onclick="js_edit_jquery.addcss(\''+nomtmp+'\', \''+nomtmp+'\')">'+
			'</div><div class="clearfix"><div class="float-left">'+
			'<label><span class="label">JS</span>'+
			'<input type="text" name="'+nomtmp+'|js[]" value=""></label>'+
			'<div id="addjsnew'+nomtmp+'"></div></div>'+
			'<input type="button" class="float-right" value="Autre JS" '+
			'onclick="js_edit_jquery.addjs(\''+nomtmp+'\', \''+nomtmp+'\')">'+
			'</div></fieldset>';
			$("#addlibrary").empty();
			$("#addlibrary").html(bloc);
		}
	}
})();

var js_edit_mail = (function(){
	return {
		"init" : function(){
			;
		},
		"add" : function(id){
			var bloc = '<fieldset>'+
			'<div><label><span>Destinataire des messages reçus : email</span>'+
			'<input type="text" name="destinataires|'+id+'|email" value=""></label></div>'+
			'<div><label><span>Destinataire des messages reçus : name</span>'+
			'<input type="text" name="destinataires|'+id+'|name" value=""></label></div>'+
			+'</fieldset>';
			$("#newDestinataire").empty();
			$("#newDestinataire").html(bloc);
		}
	}
})();