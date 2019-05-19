/**
 * scripts de la page de sbm-gestion/eleve/envoiphoto.phtml
 * 
 * L'initialisation de la structure se fait en passant l'url de retour.
 * 
 * @project sbm
 * @filesource envoiphoto.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 jan. 2019
 * @version 2019-2.4.6
 */
var js_envoi = (function(){
	var url_retour;
	function setUrlRetour(url) {
		url_retour = url;
	}
	$(function(){
		$("button[type=button][name=cancel]").click(function() {
		   location.href =url_retour;
		});	
		$("input[type=file][name=filephoto]").change(function() {
			if ($(this).val()) {
				js_envoi.montreBtnEnvoiPhoto(true);
			} else {
				js_envoi_montreBtnEnvoiPhoto(false);
			}
		});
	    $("button[type=button][name=envoiphoto]").click(function() {
	        var form = $("#formphoto");
	        var post_url = form.attr("action");
	        var request_method = form.attr("method");
	        var form_data = new FormData(document.querySelector("#formphoto"));
			var containerprogress = $(".photo-progress");
			var progressbar = $('.photo-progressbar');
			containerprogress.show();
			progressbar.css('width','0');
	        $.ajax({
	            url: post_url,
	            type: request_method,
	            data: form_data,
	            contentType: false,
	            processData: false,
	            xhr: function() {
	                var xhr = new window.XMLHttpRequest();
					//Upload progress
					xhr.upload.addEventListener("progress", function(evt){
					    if (evt.lengthComputable) {
					        var percentComplete = parseInt(100 * evt.loaded / evt.total);
					        progressbar.css('width', percentComplete + '%');
					        progressbar.text(percentComplete + '%');
					    }
					}, false);
					return xhr;
	            },
	            success: function(data) {
	                var retour = $.parseJSON(data);
	                //containerprogress.hide();
					if (retour.success == 1) {
						$("#wrapper-img").show();
						$("#wrapper-img img").attr('src', retour.src);
						$("#error").hide();
					} else {
					    $("#wrapper-img").hide();
						$("#error").html(retour.cr);
						$("#error").show();
					}
	            },
	            error: function(xhr, ajaxOptions, thrownError){
	            	alert(xhr.status + " " + thrownError);
	            }
	        }).done(function(){
	            containerprogress.hide();
	            setTimeout(function(){location.href = url_retour;},3000);      
	        });
	    });
	});
    return {
    "init" : function(url) {
    	    setUrlRetour(url);
			this.montreBtnEnvoiPhoto(false);
			$("#wrapper-img").hide();
			$("#error").hide();
		},
	"montreBtnEnvoiPhoto": function(voir) {
			var d = $("button[name=envoiphoto]");
			if (voir) {
				d.show();
			} else {
				d.hide();
			}
		}
	}
})();