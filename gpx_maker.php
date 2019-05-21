<!DOCTYPE html>
<!-- This application and the included geotools.js are licenced under the <a href="http://www.gnu.org/copyleft/gpl.html">GNU General Public Licence</a> -->
<html lang="en">
<head>
  <title>GPX and KML Drawing Tool</title>
  <meta charset="utf-8" />
  <script>
    var polyline;
    var mymap;

    function GetMap() {
        mymap = new Microsoft.Maps.Map('#myMap', { credentials: 'Put your own Bing Maps Key Here' });
        
        // viewchangestart, viewchange, viewchangeend, click, dblclick, rightclick
        // mousedown, mouseout, mouseover, mouseup, mousewheel, maptypechanged
        Microsoft.Maps.Events.addHandler(mymap, 'mouseup', function () { userFeedback(); });
        
        var url    = window.location.search;    // ?lat=52.4&lon=1.5
        // console.log(url);
        url        = url.trim();
        url        = url.replace("?lat=",  "");
        url        = url.replace("&lon=",  "|");
        url        = url.replace("&",  "|");
        var urlArr = url.split('|');
        
        // console.log(urlArr);
        
        mymap.setView({
            mapTypeId: Microsoft.Maps.MapTypeId.ordnanceSurvey,
            center: new Microsoft.Maps.Location(urlArr[0], urlArr[1]),
            zoom: 12
        });        

        var center = mymap.getCenter();

        //Create array of locations
        var coords = [center, new Microsoft.Maps.Location(center.latitude + 0.01, center.longitude)];

        //Create a polyline
        polyline = new Microsoft.Maps.Polyline(coords, {
            strokeColor: 'blue',
            strokeThickness: 4
        });

        //Add the polyline to map
        mymap.entities.push(polyline);
        
        //Load the DrawingTools module.
        Microsoft.Maps.loadModule('Microsoft.Maps.DrawingTools', function () {
            //Create an instance of the DrawingTools class and bind it to the map.
            var tools = new Microsoft.Maps.DrawingTools(mymap);

            //Pass the polyline to the drawing tools to be edited.
            tools.edit(polyline);
        });
        
        userFeedback();
    }
    
    function pad(num, size) {    //    input 7    output 007     for example
      var s = num+"";
      while (s.length < size) s = "0" + s;
      return s;
    }
    
    function saveGPXtrack() {
      hideMap();
      showSave();
      
      var lineData = polyline.getLocations();
      
      var length = getWalkLength(lineData);
          length = Math.round(length  * 100) / 100;
          length = "<b>Distance: </b>" + length + " km" + " &nbsp; " + Math.round(0.621371 * length * 100) / 100 + " miles.";
          // console.log(length);
      
      var date = new Date();
      var dateString  = date.getFullYear();
          dateString += '-' + pad(date.getMonth(), 2);
          dateString += '-' + pad(date.getDate(), 2);
          dateString += 'T' + pad(date.getHours(),  2);
          dateString += ':' + pad(date.getMinutes(),  2);
          dateString += ':' + pad(date.getSeconds(),  2);
          dateString += '.' + pad(date.getMilliseconds(),  2) + 'Z';
      
      var ii;

      var lat;
      var lon;
      
      var minlat = Math.round(lineData[0].latitude  * 1000000) / 1000000;
      var minlon = Math.round(lineData[0].longitude * 1000000) / 1000000;

      var maxlat = Math.round(lineData[0].latitude  * 1000000) / 1000000;
      var maxlon = Math.round(lineData[0].longitude * 1000000) / 1000000;
      
      var startlat = minlat;
      var startlon = minlon;
      
      var endlat   = Math.round(lineData[lineData.length - 1].latitude * 1000000) / 1000000;
      var endlon   = Math.round(lineData[lineData.length - 1].longitude * 1000000) / 1000000;
      
      var grStart = convertlatlonToGR(startlat, startlon); 
      var grEnd   = convertlatlonToGR(endlat,   endlon);
      
      for (ii = 1; ii < lineData.length; ii++) {
        lat = Math.round(lineData[ii].latitude  * 1000000) / 1000000;
        lon = Math.round(lineData[ii].longitude * 1000000) / 1000000;
        if (lat < minlat) minlat = lat;
        if (lon < minlon) minlon = lon;
        if (lat > maxlat) maxlat = lat;
        if (lon > maxlon) maxlon = lon;            
      }
      
      var gpxtxt = "";
          gpxtxt += '<gpx xmlns="http://www.topografix.com/GPX/1/0" version="1.0" creator="Waveney Ramblers GPX Maker - http://waveneyramblers.org.uk/gpx_maker.php">\n';
          gpxtxt += '<time>' + dateString + '</time>\n';    // <time>2019-04-10T17:16:02.153Z</time>
          gpxtxt += '  <bounds minlat="' + minlat + '" minlon="' + minlon + '" maxlat="' + maxlat + '" maxlon="' + maxlon + '"/>\n';
          gpxtxt += '  <wpt lat="' + startlat + '" lon="' + startlon + '">\n';
          gpxtxt += '    <ele>0.000000</ele>\n';
          gpxtxt += '    <name>Start, ' + grStart + '</name>\n';
          gpxtxt += '    <cmt>Start, ' + grStart + '</cmt>\n';
          gpxtxt += '    <desc>Start, ' + grStart + '</desc>\n';
          gpxtxt += '  </wpt>\n';
          gpxtxt += '  <wpt lat="' + endlat + '" lon="' + endlon + '">\n';
          gpxtxt += '    <ele>0.000000</ele>\n';
          gpxtxt += '    <name>End, ' + grEnd + '</name>\n';
          gpxtxt += '    <cmt>End, ' + grEnd + '</cmt>\n';
          gpxtxt += '    <desc>End, ' + grEnd + '</desc>\n';
          gpxtxt += '  </wpt>\n';
          gpxtxt += '  <trk>\n';
          gpxtxt += '    <name>Track Edit Me</name>\n';
          gpxtxt += '    <trkseg>\n';
          
          for (ii = 0; ii < lineData.length; ii++) {
            var lat = Math.round(lineData[ii].latitude  * 1000000) / 1000000;
            var lon = Math.round(lineData[ii].longitude * 1000000) / 1000000;
            
            gpxtxt += '      <trkpt lat="' + lat + '" lon="' + lon + '"></trkpt>\n';
//          gpxtxt += '        <ele>0.000000</ele>\n';
//          gpxtxt += '      </trkpt>\n';
          }
          
          gpxtxt += '    </trkseg>\n';
          gpxtxt += '  </trk>\n';
          gpxtxt += '</gpx>\n';
          
      //console.log(lineData);
      //console.log(lineData.length);
      //console.log(gpxtxt);
      
      document.getElementById('gpxDAT').innerHTML     =  gpxtxt;
      document.getElementById('KmlGpx').value         =  "gpx_track";
      document.getElementById('length').value         =  length;
      document.getElementById('showLength').innerHTML =  length;
      document.getElementById('filename').value       =  dateString;
    }
    
    function saveGPXroute() {
      hideMap();
      showSave();
      
      var lineData = polyline.getLocations();
      
      var length = getWalkLength(lineData);
          length = Math.round(length  * 100) / 100;
          length = "<b>Distance: </b>" + length + " km" + " &nbsp; " + Math.round(0.621371 * length * 100) / 100 + " miles.";
          // console.log(length);
      
      var date = new Date();
      var dateString  = date.getFullYear();
          dateString += '-' + pad(date.getMonth(), 2);
          dateString += '-' + pad(date.getDate(), 2);
          dateString += 'T' + pad(date.getHours(),  2);
          dateString += ':' + pad(date.getMinutes(),  2);
          dateString += ':' + pad(date.getSeconds(),  2);
          dateString += '.' + pad(date.getMilliseconds(),  2) + 'Z';
      
      var ii;

      var lat;
      var lon;
      
      var startlat = Math.round(lineData[0].latitude  * 1000000) / 1000000;
      var startlon = Math.round(lineData[0].longitude * 1000000) / 1000000;

      var endlat   = Math.round(lineData[lineData.length - 1].latitude * 1000000) / 1000000;
      var endlon   = Math.round(lineData[lineData.length - 1].longitude * 1000000) / 1000000;
      
      var grStart = convertlatlonToGR(startlat, startlon); 
      var grEnd   = convertlatlonToGR(endlat,   endlon);
      
      var gpxtxt = "";
          gpxtxt += '<?xml version=\'1.0\' encoding=\'ISO-8859-1\'?>\n';
          gpxtxt += '<gpx xmlns=\'http://www.topografix.com/GPX/1/1\' version=\'1.1\' creator=\'Waveney Ramblers - waveneyramblers.org.uk\'\n';
          gpxtxt += 'xmlns:xsi=\'http://www.w3.org/2001/XMLSchema-instance\'\n';
          gpxtxt += 'xsi:schemaLocation=\'http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd\'>\n';
          gpxtxt += '  <wpt lat="' + startlat + '" lon="' + startlon + '">\n';
          gpxtxt += '    <ele>0.000000</ele>\n';
          gpxtxt += '    <name>Start, ' + grStart + '</name>\n';
          gpxtxt += '    <cmt>Start, '  + grStart + '</cmt>\n';
          gpxtxt += '    <desc>Start, ' + grStart + '</desc>\n';
          gpxtxt += '  </wpt>\n';
          gpxtxt += '  <wpt lat="' + endlat + '" lon="' + endlon + '">\n';
          gpxtxt += '    <ele>0.000000</ele>\n';
          gpxtxt += '    <name>End, ' + grEnd + '</name>\n';
          gpxtxt += '    <cmt>End, '  + grEnd + '</cmt>\n';
          gpxtxt += '    <desc>End, ' + grEnd + '</desc>\n';
          gpxtxt += '  </wpt>\n';
          gpxtxt += '  <rte>\n';
          gpxtxt += '    <name><![CDATA[MyRouteEditMe]]></name>\n';
          gpxtxt += '    <type><![CDATA[Route]]></type>\n';
          
          for (ii = 0; ii < lineData.length; ii++) {
            var lat = Math.round(lineData[ii].latitude  * 1000000) / 1000000;
            var lon = Math.round(lineData[ii].longitude * 1000000) / 1000000;
            gpxtxt += '    <rtept lat="' + lat + '" lon="' + lon + '"><name>rp_' + pad(ii, 3) + '</name></rtept>\n';
          }
                   
          gpxtxt += '  </rte>\n';
          gpxtxt += '</gpx>\n';
          
      //console.log(lineData);
      //console.log(lineData.length);
      //console.log(gpxtxt);
      
      document.getElementById('gpxDAT').innerHTML     =  gpxtxt;
      document.getElementById('KmlGpx').value         =  "gpx_route";
      document.getElementById('length').value         =  length;
      document.getElementById('showLength').innerHTML =  length;
      document.getElementById('filename').value       =  dateString;
    }
    
    function saveKML() {
      hideMap();
      showSave();

      var lineData = polyline.getLocations();
      
      var length = getWalkLength(lineData);
          length = Math.round(length  * 100) / 100;
          length = "<b>Distance: </b>" + length + " km" + " &nbsp; " + Math.round(0.621371 * length * 100) / 100 + " miles.";
          // console.log(length);
      
      var date = new Date();
      var dateString  = date.getFullYear();
          dateString += '-' + pad(date.getMonth(), 2);
          dateString += '-' + pad(date.getDate(), 2);
          dateString += 'T' + pad(date.getHours(),  2);
          dateString += ':' + pad(date.getMinutes(),  2);
          dateString += ':' + pad(date.getSeconds(),  2);
          dateString += '.' + pad(date.getMilliseconds(),  2) + 'Z';
      
      var ii;

      var lat;
      var lon;
      
      var startlat = Math.round(lineData[0].latitude  * 1000000) / 1000000;
      var startlon = Math.round(lineData[0].longitude * 1000000) / 1000000;
      
      var endlat   = Math.round(lineData[lineData.length - 1].latitude * 1000000) / 1000000;
      var endlon   = Math.round(lineData[lineData.length - 1].longitude * 1000000) / 1000000;
      
      var grStart  = convertlatlonToGR(startlat, startlon); 
      var grEnd    = convertlatlonToGR(endlat,   endlon);
      
      var kmltxt   = "";
          kmltxt  += '<?xml version="1.0" encoding="UTF-8"?>\n';
          kmltxt  += '<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom">\n';
          kmltxt  += '<Document>\n';
          kmltxt  += '  <name>' + dateString + '.kml</name>\n';

          kmltxt  += '  <Style id="waypoint_n">\n';
		  kmltxt  += '    <IconStyle>\n';
		  kmltxt  += '      <Icon>\n';
          kmltxt  += '        <href>http://maps.google.com/mapfiles/kml/pal4/icon61.png</href>\n';
          kmltxt  += '      </Icon>\n';
          kmltxt  += '    </IconStyle>\n';
          kmltxt  += '  </Style>\n';
          kmltxt  += '  <Style id="waypoint_h">\n';
          kmltxt  += '    <IconStyle>\n';
          kmltxt  += '      <scale>1.2</scale>\n';
          kmltxt  += '      <Icon>\n';
          kmltxt  += '        <href>http://maps.google.com/mapfiles/kml/pal4/icon61.png</href>\n';
          kmltxt  += '      </Icon>\n';
          kmltxt  += '    </IconStyle>\n';
          kmltxt  += '  </Style>\n';          
          kmltxt  += '  <StyleMap id="waypoint">\n';
          kmltxt  += '    <Pair>\n';
          kmltxt  += '      <key>normal</key>\n';
          kmltxt  += '      <styleUrl>#waypoint_n</styleUrl>\n';
          kmltxt  += '    </Pair>\n';
          kmltxt  += '    <Pair>\n';
          kmltxt  += '      <key>highlight</key>\n';
          kmltxt  += '      <styleUrl>#waypoint_h</styleUrl>\n';
          kmltxt  += '    </Pair>\n';
          kmltxt  += '  </StyleMap>\n';

          kmltxt  += '  <Style id="lineStyle0">\n';
          kmltxt  += '    <LineStyle>\n';
          kmltxt  += '      <color>ffff0000</color>\n';
          kmltxt  += '      <width>5</width>\n';
          kmltxt  += '    </LineStyle>\n';
          kmltxt  += '  </Style>\n';
          kmltxt  += '  <Style id="lineStyle1">\n';
          kmltxt  += '    <LineStyle>\n';
          kmltxt  += '      <color>ffff0000</color>\n';
          kmltxt  += '      <width>5</width>\n';
          kmltxt  += '    </LineStyle>\n';
          kmltxt  += '  </Style>\n';
          kmltxt  += '  <StyleMap id="lineStyle">\n';
          kmltxt  += '    <Pair>\n';
          kmltxt  += '      <key>normal</key>\n';
          kmltxt  += '      <styleUrl>#lineStyle1</styleUrl>\n';
          kmltxt  += '    </Pair>\n';
          kmltxt  += '    <Pair>\n';
          kmltxt  += '      <key>highlight</key>\n';
          kmltxt  += '      <styleUrl>#lineStyle0</styleUrl>\n';
          kmltxt  += '    </Pair>\n';
          kmltxt  += '  </StyleMap>\n';
          
          kmltxt  += '  <Folder>\n';
          kmltxt  += '    <name>' + dateString + '</name>\n';
          kmltxt  += '    <open>1</open>\n';
          
          kmltxt  += '    <Placemark>\n';
          kmltxt  += '      <name>Start, ' + grStart + '</name>\n';
          kmltxt  += '      <styleUrl>#waypoint</styleUrl>\n';
          kmltxt  += '      <Point>\n';
          kmltxt  += '        <coordinates>' + startlon + ',' + startlat + ',0</coordinates>\n';
          kmltxt  += '      </Point>\n';
          kmltxt  += '    </Placemark>\n';
          
          kmltxt  += '    <Placemark>\n';
          kmltxt  += '      <name>End, ' + grEnd + '</name>\n';
          kmltxt  += '      <styleUrl>#waypoint</styleUrl>\n';
          kmltxt  += '      <Point>\n';
          kmltxt  += '        <coordinates>' + endlon + ',' + endlat + ',0</coordinates>\n';
          kmltxt  += '      </Point>\n';
          kmltxt  += '    </Placemark>\n';
          
          kmltxt  += '    <Placemark>\n';
          kmltxt  += '      <name>Path Edit Me</name>\n';
          kmltxt  += '      <styleUrl>#lineStyle</styleUrl>\n';
          kmltxt  += '      <LineString>\n';
          kmltxt  += '        <tessellate>1</tessellate>\n';
          kmltxt  += '        <coordinates>\n';          
          kmltxt  += '          ';
          for (ii = 0; ii < lineData.length; ii++) {
            var lon = Math.round(lineData[ii].longitude * 1000000) / 1000000;
            var lat = Math.round(lineData[ii].latitude  * 1000000) / 1000000;
            kmltxt += lon + ',' + lat + ' ';                          
          }          
          kmltxt  += '\n        </coordinates>\n';
          kmltxt  += '      </LineString>\n';
          kmltxt  += '    </Placemark>\n';
          kmltxt  += '  </Folder>\n';
          kmltxt  += '</Document>\n';
          kmltxt  += '</kml>\n';     
          
      //console.log(lineData);
      //console.log(lineData.length);
      //console.log(kmltxt);

      document.getElementById('gpxDAT').innerHTML     =  kmltxt;    // .split("<").join("&lt;");    // gpxtxt.replace("<", "&lt;");
      document.getElementById('KmlGpx').value         =  "kml";
      document.getElementById('length').value         =  length;
      document.getElementById('showLength').innerHTML =  length;
      document.getElementById('filename').value       =  dateString;
    }
    
    function getDistanceFromLatLonInKm(lat1,lon1,lat2,lon2) {
      var R = 6371; // Radius of the earth in km
      var dLat = deg2rad(lat2-lat1);  // deg2rad below
      var dLon = deg2rad(lon2-lon1); 
      var a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
        Math.sin(dLon/2) * Math.sin(dLon/2); 
      var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
      var d = R * c; // Distance in km
      return d;
    }

    function deg2rad(deg) {
      return deg * (Math.PI/180)
    }
    
    function getWalkLength(points) {
      var ii;
      var length = 0;
      
      for (ii = 0; ii < points.length - 1; ii++) {
        var leg = getDistanceFromLatLonInKm(points[ii].latitude,points[ii].longitude, points[ii + 1].latitude,points[ii + 1].longitude);
        length += leg;
        // console.log(leg + " " + length);
      }

      return length;
    }
    
    function convertlatlonToGR(latitude, longitude) {
      // This function uses geotools.js licenced under the <a href="http://www.gnu.org/copyleft/gpl.html">GNU General Public Licence
      var wgs84 = new GT_WGS84();
	  wgs84.setDegrees(latitude, longitude);

	  if (wgs84.isGreatBritain()) {
		var osgb=wgs84.getOSGB();
		var gridref = osgb.getGridRef(5);
		
		return gridref;
	  } else {
		return Math.round(latitude  * 100) / 100 + ", " + Math.round(longitude  * 100) / 100;
	  }  
    }
    
    function userFeedback() {
      var lineData = polyline.getLocations();
      
      var length = getWalkLength(lineData);
          length = Math.round(length  * 100) / 100;
          length = "<b>Distance: </b>" + length + " km" + " &nbsp; " + Math.round(0.621371 * length * 100) / 100 + " miles.";
          
      var startlat = Math.round(lineData[0].latitude  * 1000000) / 1000000;
      var startlon = Math.round(lineData[0].longitude * 1000000) / 1000000;
      
      var endlat   = Math.round(lineData[lineData.length - 1].latitude * 1000000) / 1000000;
      var endlon   = Math.round(lineData[lineData.length - 1].longitude * 1000000) / 1000000;
      
      var grStart;
      var grEnd;
      
      try {
        grStart  = convertlatlonToGR(startlat, startlon);
      }
      catch(err) {
      	grStart = Math.round(startlat  * 100) / 100 + ', ' + Math.round(startlon  * 100) / 100; 
      }
      
      try {
        grEnd  = convertlatlonToGR(endlat, endlon);
      }
      catch(err) {
      	grEnd = Math.round(endlat  * 100) / 100 + ', ' + Math.round(endlon  * 100) / 100; 
      }
      
      document.getElementById('but_info').innerHTML = '<b> &nbsp; &nbsp; ' + length + ' &nbsp; &nbsp; Start: ' + grStart + ' &nbsp; &nbsp; End: ' + grEnd + ' &nbsp; &nbsp; </b>';
      
      mymap.entities.clear();    // No idea why this cleans the clutter but is seems to !!!!!
    }
    
    function importPath() {
      hideMap();
      showImport();
    }
    
    function showImport() {
      document.getElementById("myImp").style.display = "block";
    }
    
    function showSave() {
      document.getElementById("myDat").style.display = "block";
    }
    
    function showMap() {
      document.getElementById("myImp").style.display        = "none";
      document.getElementById("myDat").style.display        = "none";
      document.getElementById("myMap").style.display        = "block";
      document.getElementById("but_help").style.display     = "inline";
      document.getElementById("but_info").style.display     = "inline";
      document.getElementById("but_GPXtrack").style.display = "inline";
      document.getElementById("but_GPXroute").style.display = "inline";
      document.getElementById("but_KML").style.display      = "inline";
      document.getElementById("but_IMP").style.display      = "inline";
    }

    function hideMap() {
      document.getElementById("myMap").style.display        = "none";
      document.getElementById("but_help").style.display     = "none";
      document.getElementById("but_info").style.display     = "none";
      document.getElementById("but_GPXtrack").style.display = "none";
      document.getElementById("but_GPXroute").style.display = "none";
      document.getElementById("but_KML").style.display      = "none";
      document.getElementById("but_IMP").style.display      = "none";
    }
    
    function getCoords() {
      // Remove old coords
      //for (var i = mymap.entities.getLength() - 1; i >= 0; i--) {
      //  var pline = mymap.entities.get(i);
      //  if (pline instanceof Microsoft.Maps.Polyline) {
      //    mymap.entities.removeAt(i);
      //  }
      //}
      //mymap.entities.clear();

      var newcoords = [];
      
      var xmlSource = document.getElementById('gpxIMP').value;
          xmlSource = xmlSource.trim();
      // console.log(xmlSource);
      var parser    = new DOMParser();
      var doc       = parser.parseFromString(xmlSource, "text/xml");	
    	
      console.log(doc);
      
      var xx = doc.getElementsByTagName('trkpt');
      console.log(xx);
      for (var ii = 0; ii < xx.length; ii++) {
        var ll = xx[ii]; 
        console.log('Lat = ' + ll.attributes[0].value);
        console.log('Lon = ' + ll.attributes[1].value);
        newcoords.push(new Microsoft.Maps.Location(ll.attributes[0].value, ll.attributes[1].value));
      }

      var xx = doc.getElementsByTagName('rtept');
      console.log(xx);
      for (var ii = 0; ii < xx.length; ii++) {
        var ll = xx[ii]; 
        console.log('Lat = ' + ll.attributes[0].value);
        console.log('Lon = ' + ll.attributes[1].value);
        newcoords.push(new Microsoft.Maps.Location(ll.attributes[0].value, ll.attributes[1].value));
      }

      var coords = doc.getElementsByTagName('coordinates');
      var lonlat;
      var llarray;
      for (var ii = 0; ii < coords.length; ii++) {
        var ll = coords[ii];  
        if (ll.parentElement.nodeName == 'LineString') {
          lonlat = ll.innerHTML.trim();
          llarray = lonlat.split(" ");
          console.log(lonlat);
          console.log(llarray);
          
          for (var ii = 0; ii < llarray.length; ii++) {
          	var ll = llarray[ii].split(",");
          	console.log('Lat = ' + ll[1]);
          	console.log('Lon = ' + ll[0]);
          	newcoords.push(new Microsoft.Maps.Location(ll[1], ll[0]));
          }
        }
        
        //console.log(typeof(lonLat));
        //console.log(ll.parentElement.nodeName);
        //console.log(ll.parentElement);
        //console.log();
      }
      
      polyline.setLocations(newcoords);
      
      mymap.setView({
        zoom: 11
      });        
      
      showMap();

      //polyline = new Microsoft.Maps.Polyline(newcoords, {
      //    strokeColor: 'blue',
      //    strokeThickness: 4
      //});
      //mymap.entities.push(polyline);

      
      // console.log(newcoords);
      // polyline.setLocations(newcoords);
      
      //mymap.setView({
      //  mapTypeId: Microsoft.Maps.MapTypeId.ordnanceSurvey,
      //  center: new Microsoft.Maps.Location(urlArr[0], urlArr[1]),
      //  zoom: 12
      //});        

      // mymap.entities.clear();
      
      //var co = ls.getElementsByTagName('coordinates');
      //console.log(ls);
      //console.log(co);
      //console.log(xx[0].childNodes[3].innerHTML);
      
      //for (var ii = 0; ii < xx.length; ii++) {
      //  var ll = xx[ii]; 
      //  console.log('Lat = ' + ll.attributes[0].value);
      //  console.log('Lon = ' + ll.attributes[1].value);
      //}

      //console.log(ll.attributes[1]);
      //console.log(typeof(ll.attributes[1]));
      //console.log(ll.attributes[1].value);
      // for (var name in ll.attributes[1]) { console.log(name); }
      // var ar = ll.split('lat');
      //console.log(ll);
      //for (var name in ll) { console.log(name); }
      
    }    
  </script>
  <script src='http://www.bing.com/api/maps/mapcontrol?callback=GetMap&key=Put your own Bing Maps Key Here' async defer></script>
  <script src="js/geotools.js"></script>
  <!-- Get GeoTools from:  http://www.nearby.org.uk/tests/GeoTools.html -->
</head>
<body>
    <!-- THE MAP DIV -->
    <div id="myMap" style="position:absolute;width:99%;height:99%;"></div>
    
    <!-- THE IMPORT DIV -->
    <div id="myImp" style="position:absolute;width:99%;height:99%; display:none;">
      <textarea name="gpx_import" id="gpxIMP" style="position:relative;width:98vw;height:80vh">
Open the KML or GPX file to be imported using a simple text editor like Notepad, TextEdit or Leafpad.

Select and copy the entire document and paste it into this zone of this window.

Make sure you overwrite all these instructions.

Click the "Import KML / GPX" button. You might need to pan and zoom the map to see the imported track.
      </textarea>
      <!--
      <form action="gpx_upload.php" method="post" enctype="multipart/form-data">
        Select GPX or KML file to open:
        <input type="file" name="fileToUpload" id="fileToUpload">
        <input type="submit" value="Upload GPX or KML" name="submit">
      </form>
      -->
      <p><input id="but_import" type="button" value="Import GPX / KML" onclick="getCoords()"> &nbsp; &nbsp; &nbsp; &nbsp;
         <input id="but_MAP"    type="button" value="Return to Map" onclick="showMap()"/></p>

    </div>
    
    <!-- FORM TO SAVE GPX or KML -->
    <div id="myDat" style="position:absolute;width:99%;height:99%; display:none;">
      <form action="gpx_form.php" method="post">
        <textarea name="gpx_data" id="gpxDAT" style="position:relative;width:98vw;height:80vh"></textarea>
        <input id="length"      name="length"   type="hidden">
        <input id="KmlGpx"      name="KmlGpx"   type="hidden">
        <input id="filename"    name="filename" type="hidden">
        <p id="showLength"></p>
        <p><input type="submit" name="Submit" id="but_DAT" value="Download GPX / KML"> &nbsp; &nbsp; &nbsp; &nbsp;
           <input id="but_MAP" type="button" value="Return to Map" onclick="showMap()"/></p>
      </form>
    </div>
    
    <!-- MAIN MENU BUTTONS AND FEEDBACK SPANS -->
    <div style="position:absolute; top:5px; left:5px;">
      <span id="but_info" style="background-color:#ffffff">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span>
      <span id="but_help" style="background-color:#ffffff"><b>&nbsp; &nbsp; &nbsp;<a style="text-decoration:none" href="/gpx_maker_help.php" target="_blank">HELP</a>&nbsp; &nbsp; &nbsp;</b></span><br>
      <input id="but_GPXtrack" type="button" value="Save GPX Track"    onclick="saveGPXtrack()"/><br>
      <input id="but_GPXroute" type="button" value="Save GPX Route"    onclick="saveGPXroute()"/><br>
      <input id="but_KML"      type="button" value="Save KML"          onclick="saveKML()"/><br>
      <input id="but_IMP"      type="button" value="Import KML or GPX" onclick="importPath()"/>
      <?php 
      if ((isset($_POST)) && (!empty($_POST))) { 
      	echo everything_in_tags($_POST, 'trkpt');
        echo "<pre>" . print_r($_POST, 1) . "</pre>\n"; 
      } 
      ?>
    </div>
</body>
</html>
