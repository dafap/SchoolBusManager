/**
 * Objet jQuery qui gère une carte avec des données présentées par des marqueurs cliquables pour afficher
 * leurs propriétés. Les données sont structurées sous forme de couches. Un multicheckbox commande la
 * visibilité de chaque couche.
 * USAGE : carte.init(<liste des paramètes>).icon(part1,part2); // icon() est optionnel
 *
 * Pour changer les icones des marqueurs :
 * - le dossier des images est dans url_icon_path (sans le scheme)
 * - le fichier image (png) est dans la propriété icon du lieu à marquer
 *
 * Cet objet a trois propriétés 'init', 'icon' et 'trace'
 * 'init' : 
 *     initialise l'objet en passant dans l'ordre les paramètres suivants :
 *     - myCheckboxName : nom du 'multicheckbox' qui commande l'apparition des différentes couches
 *     - myDiv : nom de la DIV qui contiendra la carte
 *     - myScheme : 'http' ou 'https'
 *     - myLatCentre : latitude du centre de la carte
 *     - myLngCentre : longitude du centre de la carte
 *     - myZoom : zoom à appliquer au chargement
 *     - myTMarkers : tableau associatif des couches
 * 'iconpath' :
 *     permet de modifier les icones utilisés pour les pointeurs. Le scheme d'accès doit être celui du site.
 *     - url : début de l'url commençant par :// et se terminant par / (path de la ressource)
 *     Le nom du fichier d'un icone est passé dans la propriété 'icon' décrivant le lieu à marquer.
 * 'trace' :
 *     construit et trace la carte avec les données correspondantes aux couches choisies. Pas de paramètre.
 * Cet objet est mis en place par l'évènement 'load' de la fenêtre window. Il est initialisé dans la page html.
 *
 * Les couches sont structurées de la manière suivante :
 *     - les valeurs des checkbox du multicheckbox sont les noms des couches
 *     - le tableau myTtMarkers a pour clés les noms des couches
 *       § Chaque couche est représentée dans myTMarkers par un tableau indexé (à partir de 0).
 *       § Chaque élément d'une couche présente les propriétés suivantes :
 *         - icon : fichier image du marqueur dans la librairie utilisée
 *         - lat : latitude du lieu
 *         - lng : longitude du lieu
 *         - title : info sur la carte au survol du marqueur
 *         - info : informations structurées en html à afficher sur clic d'un marqueur dans une fenêtre
 *
 * @project sbm
 * @filesource carte.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 oct. 2020
 * @version 2020-2.6.1
 */
var carte = function() {
	const url_icon_path_default = "://maps.google.com/mapfiles/ms/micons/";
	var checkboxName, div, scheme, lat_centre, lng_centre, zoom, tMarkers, map, markerGroups;
	var url_icon_path;
	function ajoutGroup(nomCouche) {
		var infowindow = new google.maps.InfoWindow();
		markerGroups.set(nomCouche, map);
		for (var i = 0; i < tMarkers[nomCouche].length; i++) {
			marker = new google.maps.Marker({
				position: new google.maps.LatLng(tMarkers[nomCouche][i].lat, tMarkers[nomCouche][i].lng),
				title: tMarkers[nomCouche][i].title,
				icon: scheme + url_icon_path + tMarkers[nomCouche][i].icon,
				couche: nomCouche,
				numero: i
			});
			marker.bindTo('map', markerGroups, nomCouche);
			// info windows
			google.maps.event.addListener(marker, 'click', (function(marker) {
				return function() {
					infowindow.setContent(tMarkers[marker.couche][marker.numero].info);
					infowindow.open(map, marker);
				}
			})(marker, nomCouche, i));
		}
	}
	return {
		init: function(myCheckboxName, myDiv, myScheme, myLatCentre, myLngCentre, myZoom, myTMarkers) {
			checkboxName = myCheckboxName;
			div = myDiv;
			scheme = myScheme;
			lat_centre = myLatCentre;
			lng_centre = myLngCentre;
			zoom = myZoom;
			tMarkers = myTMarkers;
			url_icon_path = url_icon_path_default;
			return this;
		},
		iconpath: function(url){
			url_icon_path = url;
			return this;
		},
		trace: function() {
			var mapOptions = {
				zoom: zoom,
				center: new google.maps.LatLng(lat_centre, lng_centre)
			};
			map = new google.maps.Map(document.getElementById(div), mapOptions);
			markerGroups = new google.maps.MVCObject();
			for (var key in tMarkers) { ajoutGroup(key); }
			// lien avec le multicheckbox de filtrage
			var chk = 'input[name=' + checkboxName + ']';
			$(chk).on('click init-' + checkboxName, function() {
				markerGroups.set(this.value, (this.checked) ? map : null)
			}).trigger('init-' + checkboxName);
		}
	}
}();
google.maps.event.addDomListener(window, 'load', carte.trace);  