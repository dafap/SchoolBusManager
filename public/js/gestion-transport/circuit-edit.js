/**
 * scripts des pages de sbm-gestion/transport/circuit-ajout.phtml et circuit-edit.phtml
 * 
 * @project sbm
 * @filesource gestion-transport/circuit-edit.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 fév. 2019
 * @version 2019-2.4.7
 */
var js_actions = (function(){
	function montreMer(visible) {
	    if (visible) {
	        $("#circuit-m2").show();
	        $("#circuit-s2").show();
	    } else {
	        $("#circuit-m2").hide();
	        $("#circuit-s2").hide();
	    }
	}
	function montreSam(visible) {
	    if (visible) {
	        $("#circuit-m3").show();
	        $("#circuit-s3").show();
	    } else {
	        $("#circuit-m3").hide();
	        $("#circuit-s3").hide();
	    }
	}
	$(document).ready(function() {
	    $(document).on("change", "input[name=checkall_box_circuit-ajout-semaine]", function(){
	        var mercredi = $("#semaine-mer");
	        var samedi = $("#semaine-sam");
	        if (!$(this).prop("indeterminate")) {
			    if (mercredi.length) {
			        montreMer($(this).is(":checked"));
			    }
			    if (samedi.length) {
			        montreSam($(this).is(":checked"));
			    }
			}
		});
		$("#semaine-mer").click(function(){
		    montreMer($(this).is(":checked"));
		});
		$("#semaine-sam").click(function(){
		    montreSam($(this).is(":checked"));
		});
		$("#comment-copy").click(function() {
		    $("#circuit-commentaire2").append($("#circuit-commentaire1").val());
		});
	});
	return {
		"init": function(){
			montreMer($("#semaine-mer").is(":checked"));
			montreSam($("#semaine-sam").is(":checked"));
		}
	}
})();