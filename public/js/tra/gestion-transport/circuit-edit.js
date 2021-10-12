/**
 * scripts des pages de sbm-gestion/transport/circuit-ajout.phtml et
 * circuit-edit.phtml
 * 
 * @project sbm
 * @filesource gestion-transport/circuit-edit.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 fév. 2020
 * @version 2020-2.6.0
 */
var js_actions = (function() {
	var serviceId;
	var stationId;
	function creerElementLegend(legend, with_checkall_box, wrapper) {
		var value;
		if (with_checkall_box) {
			value = '<span>'
					+ legend
					+ '<input type="checkbox" class="checkall_box" name="checkall_box_'
					+ wrapper + '"></span>';
		} else {
			value = legend;
		}
		return '<legend>' + value + '</legend>';
	}
	function creerElementCheckbox(name, id_input, css_input, value, id_label,
			css_label, label) {
		return '<input type="checkbox" name="' + name + '[]" id="' + id_input
				+ '" class="' + css_input + '" value="' + value + '">'
				+ '<label class="' + css_label + '" id="' + id_label
				+ '" for="' + id_input + '">' + label + '</label>';
	}
	function creerFieldsetSemaine(jours) {
		var html = creerElementLegend('Jours de passage', true,
				'wrapper-semaine');
		var joursHoraire = [];
		for (var i = 1; i < jours.length; i++) {
			var name = 'semaine';
			var id_input = 'jours-horaire' + i;
			var css_input = 'sbm-multicheckbox';
			var value = 1 << (i - 1);
			var id_label = 'label-jours-horaire' + i;
			var css_label = 'sbm-label-semaine';
			var label = jours[i];
			html = html
					+ creerElementCheckbox(name, id_input, css_input, value,
							id_label, css_label, label);
		}
		return '<fieldset>' + html + '</fieldset>';
	}
	function validServiceStation() {
		if (serviceId == null || serviceId.length == 0 || stationId == null
				|| stationId.length == 0) {
			alert('Avant d\'indiquer les horaires, choisissez un service puis une station.');
			return true;
		}
		return false;
	}
	function montreHoraires1(visible) {
		if (visible) {
			$("#horaire1").show();
		} else {
			$("#horaire1").hide();
		}
	}
	function montreHoraires2(visible) {
		if (visible) {
			$("#horaire2").show();
		} else {
			$("#horaire2").hide();
		}
	}
	function montreHoraires3(visible) {
		if (visible) {
			$("#horaire3").show();
		} else {
			$("#horaire3").hide();
		}
	}
	function setSemaine(jours) {
		for (i = 1; i <= 3; i++) {
			$("#horaire" + i).hide();
		}
		$("#wrapper-semaine").empty();
		$("#wrapper-semaine").append(creerFieldsetSemaine(jours));
	}
	/*function majSemaine(serviceId, stationId) {
		var args;
		if (serviceId == null || serviceId.length == 0)
			return setSemaine([]);
		if (stationId == null || stationId.length == 0) {
			args = 'serviceId:' + serviceId;
		} else {
			args = 'serviceId:' + serviceId + '/stationId:' + stationId;
		}
		$.ajax({
			url : '/sbmajaxtransport/tablehorairescircuit/' + args,
			dataType : 'json',
			success : function(data) {
				if (data['success'] == 1) {
					var jours = [];
					var table = data['table'];
					var i = 1;
					for ( var key in table) {
						var ligne = table[key];
						var nature = ligne['nature'];
						$("#wrapper-nature-horaire" + i).text(nature);
						$("input[name=m" + i + "]").val(ligne['m']);
						$("input[name=s" + i + "]").val(ligne['s']);
						$("input[name=z" + i + "]").val(ligne['z']);
						jours[i++] = nature;
					}
					setSemaine(jours);
				}
			},
			error : function(xhr, ajaxOptions, thrownError) {
				alert(xhr.status + ' ' + thrownError);
			}
		});
	}*/
	$(document).ready(
			function() {
				$("#circuit-serviceId").on('change', function() {
					serviceId = this.value;
					//majSemaine(serviceId, stationId);

				});
				$("#circuit-stationId").on('change', function() {
					stationId = this.value;
					//majSemaine(serviceId, stationId);
				});
				$(document).on("change",
						"input[name=checkall_box_wrapper-semaine]", function() {
							var checkbox1 = $("#jours-horaire1");
							var checkbox2 = $("#jours-horaire2");
							var checkbox3 = $("#jours-horaire3");
							if (!$(this).prop("indeterminate")) {
								if (validServiceStation()) return;
								if (checkbox1.length) {
									montreHoraires1($(this).is(":checked"));
								}
								if (checkbox2.length) {
									montreHoraires2($(this).is(":checked"));
								}
								if (checkbox3.length) {
									montreHoraires3($(this).is(":checked"));
								}
							}
						});
				$(document).on("click", "#jours-horaire1", function() {
					if (validServiceStation()) return;
					montreHoraires1($(this).is(":checked"));
				});
				$(document).on("click", "#jours-horaire2", function() {
					if (validServiceStation()) return;
					montreHoraires2($(this).is(":checked"));
				});
				$(document).on("click", "#jours-horaire3", function() {
					if (validServiceStation()) return;
					montreHoraires3($(this).is(":checked"));
				});
				$("#comment-copy").click(
						function() {
							$("#circuit-commentaire2").append(
									$("#circuit-commentaire1").val());
						});
			});
	return {
		"initajout" : function() {
			serviceId = '';
			stationId = null;
			montreHoraires1($("#jours-horaire1").is(":checked"));
			montreHoraires2($("#jours-horaire2").is(":checked"));
			montreHoraires3($("#jours-horaire3").is(":checked"));
		},
	    "initedit" : function(serId, staId) {
	    	serviceId = serId;
	    	stationId = staId;
			montreHoraires1($("#jours-horaire1").is(":checked"));
			montreHoraires2($("#jours-horaire2").is(":checked"));
			montreHoraires3($("#jours-horaire3").is(":checked"));
	    }
	}
})();