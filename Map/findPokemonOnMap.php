<!DOCTYPE html>
<html>
  <head>
    <title>Nearest Pokemon</title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

    <link rel="stylesheet" type="text/css" href="../css/styles.css" media="screen" />
        <style>
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      #map {
      position:absolute;
      height:auto;
      bottom:0;
      top:0;
      left:0;
      right:0;
      margin-top:50px; /* adjust top margin to your header height */
      }
      .controls {
        margin-top: 10px;
        border: 1px solid transparent;
        border-radius: 2px 0 0 2px;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        height: 32px;
        outline: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
      }

      #pac-input {
        background-color: #fff;
        font-family: Roboto;
        font-size: 15px;
        font-weight: 300;
        margin-left: 12px;
        padding: 0 11px 0 13px;
        text-overflow: ellipsis;
        width: 300px;
      }

      #pac-input:focus {
        border-color: #4d90fe;
      }

      .pac-container {
        font-family: Roboto;
      }

      #type-selector {
        color: #fff;
        background-color: #4d90fe;
        padding: 5px 11px 0px 11px;
      }

      #type-selector label {
        font-family: Roboto;
        font-size: 13px;
        font-weight: 300;
      }
      #target {
        width: 345px;
      }
    </style>
  </head>
  <body>

  <?php
      require_once('../database/connection.php');
      $pokemonName = $_POST['pokemonName'];
  ?>

    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
          <a class="navbar-brand" href="../index.html">WheresMyPokemon</a>
        </div>
        <div class="collapse navbar-collapse " id="myNavbar">
        <ul class="nav navbar-nav">
          <li><a href="../index.html"><span class="glyphicon glyphicon-home" aria-hidden="true"></span>Home</a></li>
          <li><a href="entireMap.php"><span class="glyphicon glyphicon-map-marker" aria-hidden="true"></span>Map</a></li>
          <li><a href="../FindPokemon/findPokemon.php"><span class="glyphicon glyphicon-search" aria-hidden="true"></span>Find Pokemon</a></li>
        </ul>
      </div>
    </nav>
    
    <input id="pac-input" class="controls" type="text" placeholder="Search Box">
    <div id="map"></div>

    <?php

      $sql = sprintf("CALL sp_selectPokemonInfoByName('%s')", $con->real_escape_string($pokemonName));

      $pokemonInfos = $con->query($sql) or die ($con->error);

      $data = array();
      while($pokemonInfo = $pokemonInfos->fetch_assoc()) {
        $data[] = $pokemonInfo;
      }
    ?>

    <script>
      // Note: This example requires that you consent to location sharing when
      // prompted by your browser. If you see the error "The Geolocation service
      // failed.", it means you probably did not give permission for the browser to
      // locate you.
      var temp_infowindow = null;
      var current_marker = null;
      var markers = [];
      var infowindows = [];
      var placeMarkers = [];
      var locations = eval('<?php echo json_encode($data) ?>');

      function initMap() {
        var map = new google.maps.Map(document.getElementById('map'), {
          center: {lat: -34.397, lng: 150.644},
          zoom: 17
        });
 
         // Create the search box and link it to the UI element.
        var input = document.getElementById('pac-input');
        var searchBox = new google.maps.places.SearchBox(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        // Bias the SearchBox results towards current map's viewport.
        map.addListener('bounds_changed', function() {
          searchBox.setBounds(map.getBounds());
        });

        
        // Listen for the event fired when the user selects a prediction and retrieve
        // more details for that place.
        searchBox.addListener('places_changed', function() {
          var places = searchBox.getPlaces();

          if (places.length == 0) {
            return;
          }

          if (current_marker){
            current_marker.setMap(null);
          }
          // Clear out the old markers.
          placeMarkers.forEach(function(marker) {
            marker.setMap(null);
          });
       // For each place, get the icon, name and location.

          var bounds = new google.maps.LatLngBounds();
          places.forEach(function(place) {
            var icon = {
              url: "https://upload.wikimedia.org/wikipedia/en/3/39/Pokeball.PNG",
              size: new google.maps.Size(71, 71),
              origin: new google.maps.Point(0, 0),
              anchor: new google.maps.Point(10, 20),
              scaledSize: new google.maps.Size(25, 25)
            };

            placeMarkers.push(new google.maps.Marker({
              map: map,
              icon: icon,
              title: place.name,
              position: place.geometry.location
            }));

            // Create a marker for each place.
            temp_infowindow = new google.maps.InfoWindow({map: map});
            temp_infowindow.setPosition(place.geometry.location);
            temp_infowindow.setContent("You're here!");

            if (place.geometry.viewport) {
              // Only geocodes have viewport.
              bounds.union(place.geometry.viewport);
            } else {
              bounds.extend(place.geometry.location);
            }
          });
          map.fitBounds(bounds);
        });       

        // Try HTML5 geolocation.
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(position) {
            var pos = {
              lat: position.coords.latitude,
              lng: position.coords.longitude
            };

            current_marker = new google.maps.Marker({
              map: map,
              icon: {
                url: "https://upload.wikimedia.org/wikipedia/en/3/39/Pokeball.PNG",
                size: new google.maps.Size(71, 71),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(10, 20),
                scaledSize: new google.maps.Size(25, 25)
              },
              title: "Current Location",
              position: pos
            });

            var current_infowindow = new google.maps.InfoWindow({map: map});
            current_infowindow.setPosition(pos);
            current_infowindow.setContent("You're here!");

            for (var i = 0; i < locations.length; i++) { 
              var hour = locations[i]['hour'];
              var AMPM = (hour>= 12 ? 'PM' : 'AM');
              if (hour > 12){
                hour -= 12;
              }
              var minute = (locations[i]['minute'] < 10 ? '0' + locations[i]['minute'] : locations[i]['minute']);
              infowindows[i] = new google.maps.InfoWindow({
                content: 'Pokemon Name: ' + '<b>' + locations[i]['pokemonName'] + '</b>' + '</br>' + 
                'Spawned around: ' + '<b>' + hour + ':' + minute + ' ' + AMPM + '</b>' + '</br>'
                + '<a href=' + '"https://www.google.com/maps/dir/current+location/' + locations[i]['latitude'] + ',' + locations[i]['longitude'] + '"' + ' target=' + '"_blank"' + 'style="text-decoration:none;">' +  'Get Directions</a>'
              }); 


              markers[i] = new google.maps.Marker({
                position: new google.maps.LatLng(locations[i]['latitude'], locations[i]['longitude']),
                map: map,
                title: locations[i]['pokemonName'],
                icon: createMarkerIcon(locations[i]['pokemonName'])
              });

              google.maps.event.addListener(markers[i], 'mouseover', (function(marker, i) {
                return function() {
                  infowindows[i].open(map, markers[i]);
                }
              })(markers[i], i));             
            

              google.maps.event.addListener(markers[i], 'mouseout', (function(i) {    
                return function() {
                  if (temp_infowindow){
                    temp_infowindow.close();
                  }
                  current_infowindow.close();
                  infowindows[i].close();
                }      
              })(i));          
            }

            map.setCenter(pos);
          }, function() {
            var infoWindow = new google.maps.InfoWindow({map: map});
            handleLocationError(true, infoWindow, map.getCenter());
          });
        } else {
          // Browser doesn't support Geolocation
          var infoWindow = new google.maps.InfoWindow({map: map});
          handleLocationError(false, infoWindow, map.getCenter());
        }
      }

      function handleLocationError(browserHasGeolocation, infoWindow, pos) {
        infoWindow.setPosition(pos);
        infoWindow.setContent(browserHasGeolocation ?
                              'Error: The Geolocation service failed.' :
                              'Error: Your browser doesn\'t support geolocation.');
      }

      function createMarkerIcon(name) {
        var name = name.toLowerCase();
        switch (name){        
          case ("farfetch'd"):
            name = "farfetchd";
            break;
          case ("mr. mime"):
            name = "mr-mime";
            break;
          case ("nidoran f"):
            name = "nidoran-f";
            break;
          case ("nidoran m"):
            name = "nidoran-m";
            break;
          default:
             break;
        }
        return {
          'url' : 'https://img.pokemondb.net/sprites/black-white/normal/' + name + '.png',
          'scaledSize' : new google.maps.Size(70, 70)
        };
      }

    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAtyWeN6mNfW3M9qgViPCE6JwQJSjRxwTo&libraries=places&callback=initMap">
    </script>
  </body>
</html>