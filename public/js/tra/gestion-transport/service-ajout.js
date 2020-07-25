/**
 * scripts de la page sbm-gestion/transport/service-ajout.phtml
 * 
 * @project sbm
 * @filesource gestion-transport/service-ajout.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 juil. 2020
 * @version 2020-2.6.0
 */
var js_ajout = (function(){
	function setOrdreValueOptions()
	{
		var ligneId = $("select[name=ligneId").children("option:selected").val();
		var sens = $("input[type=radio][name=sens]:checked").val();
		var moment = $("input[type=radio][name=moment]:checked").val();
		var href = '/sbmajaxtransport/getordrevalueoptions/ligneId:'+ligneId+'/sens:'+sens+'/moment:'+moment;
		$.ajax({
				url : href,
				type: 'GET',
				dataType: 'json',
				success : function(dataJson) {
							if (dataJson['success']==0) {
								alert(dataJson['cr']);
							} else {
								var select = "select[name=ordre]";
								$(select).empty();
								$.each(dataJson.data,function(k,d){
									$(select).append('<option value="'+k+'">'+d+'</option>');
								});
							}
						},
				error : function(xhr, ajaxOptions, thrownError) {
							alert(xhr.status + " " + thrownError);
						}
		});
	}
	$(document).ready(function() {
		$("select[name=ligneId]").change(function(){
			setOrdreValueOptions();
		});
		$("input[type=radio][name=sens]").change(function(){
			setOrdreValueOptions();
		});
		$("input[type=radio][name=moment]").change(function(){
			setOrdreValueOptions();
		});
	});
})();