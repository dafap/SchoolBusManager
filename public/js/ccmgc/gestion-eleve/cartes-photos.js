/**
 * scripts des pages sbm-gestion/eleve-gestion/photos.phtml 
 * 
 * @project sbm
 * @filesource gestion-eleve/cartes-photos.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 fév. 2019
 * @version 2019-2.4.7
 */
var js_date = (function(){
  $(document).ready(function($) {
    $("#selection").click(function() {
      js_date.montreDate();
    });
  });
  return {
    "montreDate": function() {
      if ($("#selectionradio1").is(':checked')) {
        $("#quelle-date").show();
      } else {
        $("#quelle-date").hide();
      }
    }
  }
})();
