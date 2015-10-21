﻿<?php
session_start();
if(!isset($_SESSION["user_id"])){
	header('Location: login.php');
    exit();
}
if($_SESSION["user_type"] == "3") {
	die("ERROR: Only Call Center Operators can create new incident reports!");
}
$con = mysqli_connect("localhost", "root", "", "cms");
if(mysqli_connect_errno()) {
	die("MySQL error: " . mysqli_connect_error());
}
if(isset($_POST['submit'])) {
	$name = $_POST["name"];
	$mobile = $_POST["contact"];
	$locX = $_POST["latitude"];
	$locY = $_POST["longitude"];
	$assistance = implode(",", $_POST["assistance"]);
	
	$insert = $con->prepare("INSERT INTO incidents (name, mobile, latitude, longitude, assistance_type, operator)
VALUES (?,?,?,?,?,?);");
    $insert->bind_param("siddsi", $name, $mobile, $locX, $locY, $assistance, $_SESSION["user_id"]);
    $insert->execute();
	$rows = $insert->affected_rows;
	
	if($rows == 1) {
		
		/* ---------------------------------------------------------------------------------------
		 * FACEBOOK, TWITTER, EMAIL API GOES HERE AFTER INCIDENT ADDED INTO DATABASE SUCCESSFULLY
		 * 
		 * Available datas in PHP:
		 * $name ==> name of caller
		 * $mobile ==> mobile no
		 * $locX ==> X coordinates
		 * $locY ==> Y coordinates
		 * $assistance ==> comma seperated values for assistance type (e.g. 1,2 or 1,2,3 or 2,3 etc. - refer below line)
		 * LEGEND: 1 = Emergency Ambulance, 2 = Rescue & Evac, 3 = Fire Fighting
		 * 
		 * if want to FB post or tweet gmap available to public, can use this url below:
		 * http://maps.google.com/maps?q=1.354625,103.818740&z=20
		 *            q ==> locX,locY
		 *            z ==> Zoom level integer eg. 20
		 *            (Change accordingly to your requirements)
		 * ---------------------------------------------------------------------------------------
		 */
		
	}
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>New Incident Report :: Crisis Management System</title>
    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/sb-admin.css" rel="stylesheet">
    <!-- Custom Fonts -->
    <link href="fonts/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
    <!-- Loading Google Map API engine v3 -->
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&sensor=false"></script>
    <!-- Map Script -->
    <script>
      var map;
      var marker = null;
      var mapTypeIds = [];
      for(var type in google.maps.MapTypeId) {
        mapTypeIds.push(google.maps.MapTypeId[type]);
      }
      mapTypeIds.push("OSM");
      function initialize() {
        var mapOptions = {
          zoom: 12,
          minZoom: 12,
          maxZoom: 20,
          disableDefaultUI: false,
          center: new google.maps.LatLng(1.354625,103.818740), //center on Singapore
          mapTypeId: "OSM",
          mapTypeControlOptions: {
            mapTypeIds: mapTypeIds
          }
        };
        map = new google.maps.Map(document.getElementById('map'), mapOptions);
        map.mapTypes.set("OSM", new google.maps.ImageMapType({
          getTileUrl: function(coord, zoom) {
            return "http://tile.openstreetmap.org/" + zoom + "/" + coord.x + "/" + coord.y + ".png";
          },
          tileSize: new google.maps.Size(256, 256),
          name: "OpenStreetMap",
          maxZoom: 18
        }));
        // Create the search box and link it to the UI element.
        var input = document.getElementById('pac-input');
        var searchBox = new google.maps.places.SearchBox(input);
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
          if (marker) { marker.setMap(null);$("#latitude").val('');$("#longitude").val('');}
          // Clear out the old markers.
          markers.forEach(function(marker){marker.setMap(null);});
          markers = [];
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
            var marker2 = new google.maps.Marker({
              map: map,
              icon: icon,
              title: place.name,
              position: place.geometry.location
            });
            // Create a marker for each place.
            markers.push(marker2);
      
            google.maps.event.addListener(marker2, 'click', function () {
              $("#latitude").val(marker2.getPosition().toUrlValue().split(',')[0]);
              $("#longitude").val(marker2.getPosition().toUrlValue().split(',')[1]);
              $("#latitude").blur();$("#longitude").blur();$("#pac-input").val('');
              if (marker){marker.setMap(null);}
              marker = new google.maps.Marker({ position: marker2.getPosition(), map: map});
            });
      
            if (place.geometry.viewport) {
            // Only geocodes have viewport.
              bounds.union(place.geometry.viewport);
            } else {
              bounds.extend(place.geometry.location);
            }
          });
          map.fitBounds(bounds);
        });
      
        google.maps.event.addListener(map, 'click', function(event) {
        //call function to create marker
        $("#latitude").val(event.latLng.toUrlValue().split(',')[0]);
        $("#longitude").val(event.latLng.toUrlValue().split(',')[1]);
        $("#latitude").blur();$("#longitude").blur();$("#pac-input").val('');
        if (marker){marker.setMap(null);}
        marker = new google.maps.Marker({ position: event.latLng, map: map});
      });
      }  
      google.maps.event.addDomListener(window, 'load', initialize);
    </script>
  </head>
  <body>
    <div id="wrapper">
    <!-- Navigation -->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="index.php" style="letter-spacing:0.2em;color:#fff;-webkit-font-smoothing: subpixel-antialiased;-webkit-font-smoothing: antialiased;"><span style="color:red;font-weight:700">CRISIS</span> MANAGEMENT SYSTEM</a>
      </div>
      <!-- Top Menu Items -->
      <ul class="nav navbar-right top-nav">
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> &nbsp;<?php echo $_SESSION["user_name"]; ?> (<?php echo $_SESSION["user_type_name"]; ?>) <b class="caret"></b></a>
          <ul class="dropdown-menu">
            <li>
              <a href="login.php"><i class="fa fa-fw fa-power-off"></i> Log Out</a>
            </li>
          </ul>
        </li>
      </ul>
      <!-- Sidebar Menu Items - These collapse to the responsive navigation menu on small screens -->
      <div class="collapse navbar-collapse navbar-ex1-collapse">
        <ul class="nav navbar-nav side-nav">
          <li>
            <a href="index.php"><i class="fa fa-fw fa-dashboard"></i> Dashboard</a>
          </li>
          <li class="active">
            <a href="create.php"><i class="fa fa-fw fa-edit"></i> New Report</a>
          </li>
          <li>
            <a href="view_reports.php"><i class="fa fa-flag"></i> &nbsp;View All Reports</a>
          </li>
          <li>
            <a href="email_log.php"><i class="fa fa-fw fa-envelope"></i> Email Logs</a>
          </li>
        </ul>
      </div>
      <!-- /.navbar-collapse -->
    </nav>
    <div id="page-wrapper">
      <div class="container-fluid">
      <?php
      if(isset($_POST['submit'])) {
      	if($rows == 1) {
      		echo '<div class="alert alert-success" style="margin:10px 0 -5px 0;"><i class="fa fa-check"></i> Incident report created successfully!</div>';
      	}
      	else {
      		echo '<div class="alert alert-danger" style="margin:10px 0 -5px 0;"><i class="fa fa-exclamation-triangle"></i> <b>ERROR INSERTING INCIDENT INTO DATABASE</b></div>';
      	}
      }
	  ?>
        <!-- Page Heading -->
        <div class="row">
          <div class="col-lg-12">
            <h1 class="page-header">
              New Incident Report
            </h1>
          </div>
        </div>
        <!-- /.row -->
        <form role="form" action="create.php" method="POST">
          <div class="panel panel-default">
            <div class="panel-body">
              <div class="row">
                <div class="col-lg-4">
        <div class="form-group">
        <label for="name">Name of Caller</label>
        <input class="form-control" id="name" name="name" data-validation="length" data-validation-length="2-50" autofocus />
        </div>
        <div class="form-group">
        <label for="contact">Contact Number</label>
        <input class="form-control" id="contact" name="contact" type="number" data-validation="number" data-validation-allowing="range[80000000;99999999]" />
        </div>
        <div class="form-group">
        <label>Type of Assistance</label>
        <div class="checkbox">
        <label>
        <input type="checkbox" name="assistance[]" value="1" data-validation="checkbox_group" data-validation-qty="1-3">Emergency Ambulance
        </label>
        </div>
        <div class="checkbox">
        <label>
        <input type="checkbox" name="assistance[]" value="2">Rescue and Evacuation
        </label>
        </div>
        <div class="checkbox">
        <label>
        <input type="checkbox" name="assistance[]" value="3">Fire Fighting
        </label>
        </div>
        </div>
        </div>
        <!-- /.col-lg-6 (nested) -->
        <div class="col-lg-8">
        <div class="row" style="margin-bottom:0!important;">
        <div class="form-group col-lg-8">
        <label for="pac-input">Location</label>
        <div class="form-group input-group">
        <input id="pac-input" type="text" class="form-control" placeholder="Search for any location by typing here...">
        <span class="input-group-btn">
        <button class="btn btn-default" type="button"><i class="fa fa-search"></i>
        </button>
        </span>
        </div>
        </div>
        <div class="form-group col-lg-2">
        <label for="latitude">Latitude</label>
        <input class="form-control" type="text" id="latitude" name="latitude" data-validation="required" onclick="this.blur()" />
        </div>
        <div class="form-group col-lg-2">
        <label for="longitude">Longitude</label>
        <input class="form-control" type="text" id="longitude" name="longitude" data-validation="required" onclick="this.blur()" />
        </div>
        </div>
        <div class="row"><div class="form-group col-lg-12">                               
        <div id="map"></div></div>
        </div>
        <!-- /.col-lg-6 (nested) -->
        </div>
        <!-- /.row (nested) -->
        <div class="form-group col-lg-6" style="margin-top:15px"><button type="submit" name="submit" class="btn btn-lg btn-success btn-block"><i class="fa fa-plus"></i>&nbsp; Create Incident Report</button></div>
        <div class="form-group col-lg-6" style="margin-top:15px"><button type="submit" onclick="if(confirm('Are you sure you want to cancel creating this report?')) location.href='index.php';return false;" class="btn btn-lg btn-danger btn-block"><i class="fa fa-times"></i>&nbsp; Cancel</button></div>
        </div>
        <!-- /.panel-body -->
        </div>
        </form>
        </div>
        <!-- /.container-fluid -->
      </div>
      <!-- /#page-wrapper -->
    </div>
    <!-- /#wrapper -->
    <!-- jQuery -->
    <script src="js/jquery-2.1.4.min.js"></script>
    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/form-validator/jquery.form-validator.min.js"></script>
    <script>
      /* important to locate this script AFTER the closing form element, so form object is loaded in DOM before setup is called */
      var myLanguage = {
        errorTitle: 'Form submission failed!',
        requiredFields: 'Please click on the map below to set a location',
        lengthBadStart: 'The name must be between ',
        lengthBadEnd: ' characters',
        badInt: 'The number must start with either "8" or "9" and have exactly 8 digits',
        badAlphaNumericExtra: ' and ',
        groupCheckedRangeStart: 'You must choose between ',
        groupCheckedEnd: ' type(s) of assistance'
      };
      $.validate({
        language : myLanguage
      });
    </script>
  </body>
</html>