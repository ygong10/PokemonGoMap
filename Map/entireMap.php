<!DOCTYPE html>
<html>
  <head>
    <title>World Map</title>
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
  </head>
  <body>
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
        <li class="active"><a href="entireMap.php"><span class="glyphicon glyphicon-map-marker" aria-hidden="true"></span>Map</a></li>
        <li><a href="../FindPokemon/findPokemon.php">Find Pokemon</a></li>
      </ul>
    </div>
  </nav>
    <div id="map"></div>

    <?php
      include '../database/retrieveAllPokemonInfo.php';
    ?>

    <script>
      // Note: This example requires that you consent to location sharing when
      // prompted by your browser. If you see the error "The Geolocation service
      // failed.", it means you probably did not give permission for the browser to
      // locate you.
      var locations = eval('<?php echo json_encode($data) ?>');

      function initMap() {
        var map = new google.maps.Map(document.getElementById('map'), {
          center: {lat: -34.397, lng: 150.644},
          zoom: 17
        });
        var infoWindow = new google.maps.InfoWindow({map: map});

        // Try HTML5 geolocation.
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(position) {
            var pos = {
              lat: position.coords.latitude,
              lng: position.coords.longitude
            };

            var markers = [];
            var infowindows = [];
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
                'Spawned Around: ' + '<b>' + hour + ':' + minute + ' ' + AMPM + '</b>' + '</br>'
                + '<a href=' + '"https://www.google.com/maps/dir/current+location/' + locations[i]['latitude'] + ',' + locations[i]['longitude'] + '"' + ' target=' + '"_blank"' + 'style="text-decoration:none;">' +  'Get Directions</a>'

              }); 


              markers[i] = new google.maps.Marker({
                position: new google.maps.LatLng(locations[i]['latitude'], locations[i]['longitude']),
                map: map,
                title: locations[i]['pokemonName'],
                icon: createMarkerIcon(locations[i]['pokemonName'])
              });

              google.maps.event.addListener(markers[i], 'click', (function(marker, i) {
                return function() {
                  infowindows[i].open(map, markers[i]);
                }
              })(markers[i], i));   

              google.maps.event.addListener(map, 'click', (function(i) {    
                return function() {
                  infowindows[i].close();
                }      
              })(i));          
            }

            map.setCenter(pos);
          }, function() {
            handleLocationError(true, infoWindow, map.getCenter());
          });
        } else {
          // Browser doesn't support Geolocation
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
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAtyWeN6mNfW3M9qgViPCE6JwQJSjRxwTo&callback=initMap">
    </script>
  </body>
</html>