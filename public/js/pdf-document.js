/**
 * Ensemble des scripts des pages de sbm-pdf : pdf-edit.phtml
 * 
 * @project sbm
 * @filesource pdf-document.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 août 2016
 * @version 2016-2.1.10
 */
var js_document_edit = (function() {
	/**
	 * méthode privée voirBouton
	 * 
	 * @param disposition
	 *            prend les valeurs 'Tabulaire', 'Texte' ou 'Etiquette'
	 */
	function voirBouton(disposition) {
		var documentId = $("#documentId").val();
		$("#documentpdf-tableau").hide();
		$("#documentpdf-texte").hide();
		$("#documentpdf-etiquette").hide();
		if (documentId != '' && documentId != 0) {
			if (disposition == "Tabulaire") {
				$("#documentpdf-tableau").show();
			} else if (disposition == "Texte") {
				$("#documentpdf-texte").show();
			} else if (disposition == "Etiquette") {
				$("#documentpdf-etiquette").show();
			}
		}
	}
	;
	/**
	 * méthode privée voirRecordSource (aucun paramètre)
	 */
	function voirRecordSource() {
		if ($("#documentpdf_recordSourceType").val() == 'T') {
			$("#RrecordSource").hide();
			$("#TrecordSource").show();
		} else {
			$("#TrecordSource").hide();
			$("#RrecordSource").show();
		}
	}

	$(document).ready(function($) {
		$("#documentpdf-disposition").change(function() {
			voirBouton($(this).val());
		});
		$("#documentpdf_recordSourceType").change(function() {
			voirRecordSource();
		});
	});
	return {
		"init" : function() {
			tinymce
					.init({
						selector : "textarea.wysiwyg",
						language : "fr_FR",
						menubar : false,
						statusbar : false,
						plugins : "advlist autolink autoresize charmap code hr insertdatetime nonbreaking searchreplace spellchecker table textcolor wordcount",
						autoresize_min_height : 300,
						autoresize_max_height : 400,
						insertdatetime_formats : [ "%d/%m/%Y", "%H:%M" ],
						spellchecker_languages : "+Français=fr"
					});
			$("#accordion").accordion();
			voirRecordSource();
			voirBouton($("#documentpdf-disposition").val());
		}
	};
})();
