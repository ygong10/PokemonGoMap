<!DOCTYPE html>
<html>
  <head>
    <title>Find Pokemon</title>
    <meta name="viewport" content="initial-scale=1.0">
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
    body {
      padding-top: 100px;
      margin-left: 50px;
    }
    </style>
  </head>
  <body>

  <?php
    require_once '../database/connection.php';
  ?>
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
        <li><a href="../index.html"><span class="glyphicon glyphicon-home" aria-hidden="true"></span>Home</a></li>
        <li><a href="../Map/spawnMap.php"><span class="glyphicon glyphicon-map-marker" aria-hidden="true"></span>Map</a></li>
      </ul>
    </div>
  </nav>

  <form action="../Map/findPokemonOnMap.php" method="post" enctype='multipart/form-data'>
    <div class="form-group row">
      <label class="col-sm-2 form-control-label">Pokemon Name</label>
      <div class="col-sm-10">
        <select class="form-control" name="pokemonName" style="width:30%;">
          <?php 
            $sqlNames = "CALL sp_selectAllPokemonName";
            $names = $con->query($sqlNames);
            while ($name = $names->fetch_assoc()){
              echo "<option value= '".$name['pokemonName']."'>".$name['pokemonName']."</option>";
            }
          ?>
        </select>
      </div>
    </div>
    <div class="form-group row">
      <div class="col-sm-offset-2 col-sm-10">
        <button type="submit" class="btn btn-primary">OK</button>
      </div>
    </div>
  </form>
  </body>
</html>