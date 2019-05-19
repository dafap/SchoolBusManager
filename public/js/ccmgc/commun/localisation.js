/**
 * Présente une carte avec une liste de marqueurs et un marqueur à déplacer pour
 * relever sa position. La carte est centrée en CENTRE_LAT, CENTRE_LNG et le
 * zoom est INI_ZOO. Ces constantes doivent être définies.
 * 
 * @project sbm
 * @filesource localisation.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 mai 2019
 * @version 2016-2.1.10
 */

/**
 * @param titre
 *            contenu de l'infobulle fermée du marqueur à déplacer
 * @param description
 *            contenu de l'infobulle ouverte du marqueur à déplacer
 * @param tMarker
 *            tableau des points à placer (ensemble des autres données)
 */
function initialiser(scheme,titre,description,tMarkers) {
	var marker;
	var latlng = new google.maps.LatLng(CENTRE_LAT, CENTRE_LNG);
	// initialisation des paramètres de la carte
	var options = {
		center : latlng,
		zoom : INI_ZOOM,
		// mapTypeId: google.maps.MapTypeId.ROADMAP
		mapTypeId : "OSM",
		mapTypeControl : true,
		mapTypeControlOptions : {
			mapTypeIds : [ "OSM",
			// google.maps.MapTypeId.ROADMAP,
			// google.maps.MapTypeId.SATELLITE,
			google.maps.MapTypeId.HYBRID, google.maps.MapTypeId.TERRAIN ],
			style : google.maps.MapTypeControlStyle.DROPDOWN_MENU
		},
		streetViewControl : true
	};
	var oCarte = new google.maps.Map(document.getElementById('carte-inner'),
			options);
	// define OSM map type pointing at the OpenStreetMap tile server
	oCarte.mapTypes.set("OSM", new google.maps.ImageMapType({
		getTileUrl : function(coord, zoom) {
			return scheme+"://tile.openstreetmap.org/" + zoom + "/" + coord.x
					+ "/" + coord.y + ".png";
		},
		tileSize : new google.maps.Size(256, 256),
		name : "OpenStreetMap",
		maxZoom : 18,
		opacity : 1,
		alt : "Carte OpenStreetMap"
	}));
	// info bulle
	var oInfo = new google.maps.InfoWindow();
	// placement des markers
	for (var i = 0; i < tMarkers.length; i++) {
		var optionsMarker = {
			'styleIcon' : new StyledIcon(StyledIconTypes.BUBBLE, {
				color : tMarkers[i].color,
				text : tMarkers[i].text
			}),
			'map' : oCarte,
			'position' : new google.maps.LatLng(tMarkers[i].lat,
					tMarkers[i].lng),
			'numero' : i,
			'title' : tMarkers[i].title
		}
		var oMarker = new StyledMarker(optionsMarker);
		// listeners
		google.maps.event.addListener(oMarker, 'click', function(data) {
			oInfo.setContent(tMarkers[this.numero].info);
			oInfo.open(this.getMap(), this);
		});
	}
	// initialisation du marker
	var ptLat = document.getElementById('lat').value;
	var ptLng = document.getElementById('lng').value;
	if (!ptLat && !ptLng) {
		ptLat = CENTRE_LAT;
		ptLng = CENTRE_LNG;
	}
	var optionsMarqueur = {
		map : oCarte,
		position : new google.maps.LatLng(ptLat, ptLng),
		title : titre
	}
	marker = new google.maps.Marker(optionsMarqueur);

	var contentMarker = description;

	var infoWindow = new google.maps.InfoWindow({
		content : contentMarker,
		position : latlng
	});
	google.maps.event.addListener(marker, 'click', function() {
		infoWindow.open(oCarte, marker);
	});

	google.maps.event.addListener(oCarte, 'click', function(event) {
		var latlng = event.latLng;
		document.getElementById('lat').value = latlng.lat();
		document.getElementById('lng').value = latlng.lng();
		if (marker) {
			marker.setPosition(latlng);
		} else {
			var optionsMarker = {
				map : oCarte,
				position : new google.maps.LatLng(latlng.lat(), latlng.lng()),
				title : titre
			}
			marker = Marker(optionsMarker);
		}
	});

	infoWindow.open(oCarte, marker);
}