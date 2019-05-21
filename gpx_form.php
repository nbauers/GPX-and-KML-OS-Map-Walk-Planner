<?php

  if ($_POST['KmlGpx'] == "gpx_track") {
    header('Content-type: application/gpx+xml');
    header("Content-Disposition: attachment; filename=\"" . $_POST['filename']. ".t.gpx");  
    echo trim($_POST['gpx_data']);
  } else if ($_POST['KmlGpx'] == "gpx_route") {
    header('Content-type: application/gpx+xml');
    header("Content-Disposition: attachment; filename=\"" . $_POST['filename']. ".r.gpx");  
    echo trim($_POST['gpx_data']);
  } else if ($_POST['KmlGpx'] == "kml") {
    header('Content-type: application/vnd.google-earth.kml+xml');
    header("Content-Disposition: attachment; filename=\"" . $_POST['filename']. ".kml");  
    echo trim($_POST['gpx_data']);
  } else {
    echo "<h1>ERROR: Data type not recognised. Please contact Neil to get it fixed.</h1>";
  }
?>
