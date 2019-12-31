/**
 * Présente une carte avec une liste de marqueurs : aucune action sur cette carte.
 * La carte est centrée en CENTRE_LAT, CENTRE_LNG et le eoom est INI_ZOO. 
 * Ces constantes doivent être définies.
 * 
 * @project sbm
 * @filesource localisation.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 04 juin 2019
 * @version 2019-2.5.0
 */
function initialiser(scheme,tMarkers) {
    // initialisation des paramètres de la carte
    var options = {
        'center': new google.maps.LatLng(CENTRE_LAT, CENTRE_LNG),
        'zoom': INI_ZOOM,
        //'mapTypeId': google.maps.MapTypeId.ROADMAP
        mapTypeId: "OSM",
        mapTypeControl: true,
        mapTypeControlOptions: {
            mapTypeIds: [
                "OSM",
                //google.maps.MapTypeId.ROADMAP, 
                //google.maps.MapTypeId.SATELLITE, 
                google.maps.MapTypeId.HYBRID, 
                google.maps.MapTypeId.TERRAIN
            ],
            style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
        },
        streetViewControl: true
    };
    var oCarte = new google.maps.Map(document.getElementById('carte-inner'), options);
    // define OSM map type pointing at the OpenStreetMap tile server
    oCarte.mapTypes.set("OSM", new google.maps.ImageMapType({
        getTileUrl: function(coord, zoom) {
            return scheme+"://tile.openstreetmap.org/" + zoom + "/" + coord.x + "/" + coord.y + ".png";
        },
        tileSize: new google.maps.Size(256,256),
        name: "Carte",
        maxZoom: 18,
        opacity: 1,
        alt: "Carte OpenStreetMap"
    }));
    // info bulle
    var oInfo = new google.maps.InfoWindow();
    // placement des markers
    for (var i = 0; i < tMarkers.length; i++) {
        var optionsMarker = {
            'styleIcon': new StyledIcon(StyledIconTypes.BUBBLE,{color:tMarkers[i].color,text:tMarkers[i].text}),
            'map': oCarte,
            'position': new google.maps.LatLng( tMarkers[i].lat, tMarkers[i].lng),
            'numero': i,
            'title': tMarkers[i].title
        }
        var oMarker = new StyledMarker(optionsMarker);        
        // listeners
        google.maps.event.addListener(oMarker, 'click', function(data) {
            oInfo.setContent(tMarkers[this.numero].info);
            oInfo.open(this.getMap(), this);
        });
    }
}