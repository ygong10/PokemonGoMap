<!DOCTYPE html>
<html>
  <head>
    <title>Locate On Map</title>
    <meta name="viewport" content="initial-scale=1.0">
    <meta charset="utf-8">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
    <style>
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
      #map {
        height: 100%;
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
    <?php
    //capture the POST vars
    $pokemonName = $_POST['pokemonName'];
    $hour = $_POST['hour'];
    $minute = $_POST['minute'];
    $AMPM = $_POST['AMPM'];
    if ($AMPM == "AM"){
      if ($hour == 12){
        $hour = 0;
      }
    }else{
      if ($hour < 12){
        $hour += 12;
      }
    }
    ?>
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
          <a class="navbar-brand" href="../index.html">Where's My Pokemon</a>
        </div>
        <div class="collapse navbar-collapse " id="myNavbar">
        <ul class="nav navbar-nav">
          <li><a href="../index.html"><span class="glyphicon glyphicon-map-home" aria-hidden="true"></span>Home</a></li>
          <li><a href="entireMap.php"><span class="glyphicon glyphicon-map-marker" aria-hidden="true"></span>Map</a></li>
        </ul>
      </div>
    </nav>

    <input id="pac-input" class="controls" type="text" placeholder="Search Box">
    <div id="map"></div>
    <div></div>
    <script>
      var map;
      // initialize the google map
      function initMap() {

        var pos = {
          lat: -34.397, 
          lng: 150.644
        }
        map = new google.maps.Map(document.getElementById('map'), {
          center: {lat: pos.lat, lng: pos.lng},
          zoom: 17
        });  

        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(position) {
            var pos = {
              lat: position.coords.latitude,
              lng: position.coords.longitude
            };
            map.setCenter(pos);
          });        
        }

        // Create the search box and link it to the UI element.
        var input = document.getElementById('pac-input');
        var searchBox = new google.maps.places.SearchBox(input);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        // Bias the SearchBox results towards current map's viewport.
        map.addListener('bounds_changed', function() {
          searchBox.setBounds(map.getBounds());
        });

        var markers = [];
        // Listen for the event fired when the user selects a prediction and retrieve
        // more details for that place.
        searchBox.addListener('places_changed', function() {
          var places = searchBox.getPlaces();

          if (places.length == 0) {
            return;
          }

          // For each place, get the icon, name and location.
          var bounds = new google.maps.LatLngBounds();
          places.forEach(function(place) {
            var icon = {
              url: place.icon,
              size: new google.maps.Size(71, 71),
              origin: new google.maps.Point(0, 0),
              anchor: new google.maps.Point(17, 34),
              scaledSize: new google.maps.Size(25, 25)
            };

            // Create a marker for each place.
            markers.push(new google.maps.Marker({
              map: map,
              icon: icon,
              title: place.name,
              position: place.geometry.location
            }));

            if (place.geometry.viewport) {
              // Only geocodes have viewport.
              bounds.union(place.geometry.viewport);
            } else {
              bounds.extend(place.geometry.location);
            }
          });
          map.fitBounds(bounds);
        });

        // listens for clicks on the google map
        google.maps.event.addListener(map,'click', function(event) {          
          addMarker(event.latLng, map);
        });
      }

      // adds markers onto the map
      function addMarker(location, map) {
        // Add the marker at the clicked location, and add the next-available label
        // from the array of alphabetical characters.
        var marker = new google.maps.Marker({
          position: location,          
          map: map,
          icon: createMarkerIcon('<?= $pokemonName ?>')
        });

        $.ajax({
          url: '../database/addPokemonInfo.php',
          type: 'POST',
          data: { lat: location.lat(),
                  long : location.lng(),
                  pokemonName : '<?= $pokemonName ?>',
                  hour : '<?= $hour ?>',
                  minute: '<?= $minute ?>',
                  success : function(data){
                      alert("Location added!");
                      window.location.href = "../index.html";
                  }
                }
        });
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
          default:
             break;
        }
        return {
          'url' : 'https://img.pokemondb.net/sprites/black-white/normal/' + name + '.png',
          'scaledSize' : new google.maps.Size(70, 70)
        };
      }

    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAtyWeN6mNfW3M9qgViPCE6JwQJSjRxwTo&libraries=places&callback=initMap"
    async defer></script>
  </body>
</html>