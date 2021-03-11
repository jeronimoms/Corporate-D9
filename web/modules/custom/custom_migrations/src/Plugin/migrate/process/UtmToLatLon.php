<?php

namespace Drupal\custom_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Splits the source string into an array of strings, using a delimiter.
 *
 * This plugin creates an array of strings by splitting the source parameter on
 * boundaries formed by the delimiter.
 *
 * Available configuration keys:
 * - source: The source string.
 * - delimiter: (optional)The boundary string.
 *
 * Example:
 *
 * @code
 * process:
 *   bar:
 *     plugin: utm_to_lat_lon
 *     source: utm
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "utm_to_lat_lon"
 * )
 */
class UtmToLatLon extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {    
    if (!is_array($value)) {
      throw new MigrateException('Input should be an array.');
    }
    if (isset($value[0])) {
      $east = $value[0];
    } else {
      throw new MigrateException('UTM north is empty');
    }
    
    if (isset($value[1])) {
      $north = $value[1];
    } else {
      throw new MigrateException('UTM east is empty');
    }
    $utmZone = isset($value[2]) ? $value[2] : 30;
    
    return utmToLatLon($north, $east, $utmZone);
  }

}

/**
 * Converts UTM to LatLong coords.
 *
 * @param  UTM north coordinate.
 * @param  UTM east coordinate.
 * @param  UTM zone.
 *
 * @return Return and array with the latitude and longitude data.
 */
function utmToLatLon($north, $east, $utmZone) {

  // This is the lambda knot value in the reference
  $LngOrigin = Deg2Rad($utmZone * 6 - 183);

  // The following set of class constants define characteristics of the
  // ellipsoid, as defined my the WGS84 datum.  These values need to be
  // changed if a different dataum is used.    

  $FalseNorth = 0;   // South or North?
  //if (lat < 0.) FalseNorth = 10000000.  // South or North?
  //else          FalseNorth = 0.   

  $Ecc = 0.081819190842622;       // Eccentricity
  $EccSq = $Ecc * $Ecc;
  $Ecc2Sq = $EccSq / (1. - $EccSq);
  $Ecc2 = sqrt($Ecc2Sq);      // Secondary eccentricity
  $E1 = ( 1 - sqrt(1-$EccSq) ) / ( 1 + sqrt(1-$EccSq) );
  $E12 = $E1 * $E1;
  $E13 = $E12 * $E1;
  $E14 = $E13 * $E1;

  $SemiMajor = 6378137.0;         // Ellipsoidal semi-major axis (Meters)
  $FalseEast = 500000.0;          // UTM East bias (Meters)
  $ScaleFactor = 0.9996;          // Scale at natural origin

  // Calculate the Cassini projection parameters

  $M1 = ($north - $FalseNorth) / $ScaleFactor;
  $Mu1 = $M1 / ( $SemiMajor * (1 - $EccSq/4.0 - 3.0*$EccSq*$EccSq/64.0 - 5.0*$EccSq*$EccSq*$EccSq/256.0) );

  $Phi1 = $Mu1 + (3.0*$E1/2.0 - 27.0*$E13/32.0) * sin(2.0*$Mu1);
    + (21.0*$E12/16.0 - 55.0*$E14/32.0)           * sin(4.0*$Mu1);
    + (151.0*$E13/96.0)                          * sin(6.0*$Mu1);
    + (1097.0*$E14/512.0)                        * sin(8.0*$Mu1);

  $sin2phi1 = sin($Phi1) * sin($Phi1);
  $Rho1 = ($SemiMajor * (1.0-$EccSq) ) / pow(1.0-$EccSq*$sin2phi1,1.5);
  $Nu1 = $SemiMajor / sqrt(1.0-$EccSq*$sin2phi1);

  // Compute parameters as defined in the POSC specification.  T, C and D

  $T1 = tan($Phi1) * tan($Phi1);
  $T12 = $T1 * $T1;
  $C1 = $Ecc2Sq * cos($Phi1) * cos($Phi1);
  $C12 = $C1 * $C1;
  $D  = ($east - $FalseEast) / ($ScaleFactor * $Nu1);
  $D2 = $D * $D;
  $D3 = $D2 * $D;
  $D4 = $D3 * $D;
  $D5 = $D4 * $D;
  $D6 = $D5 * $D;

  // Compute the Latitude and Longitude and convert to degrees
  $lat = $Phi1 - $Nu1*tan($Phi1)/$Rho1 * ( $D2/2.0 - (5.0 + 3.0*$T1 + 10.0*$C1 - 4.0*$C12 - 9.0*$Ecc2Sq)*$D4/24.0 + (61.0 + 90.0*$T1 + 298.0*$C1 + 45.0*$T12 - 252.0*$Ecc2Sq - 3.0*$C12)*$D6/720.0 );

  $lat = Rad2Deg($lat);

  $lon = $LngOrigin + ($D - (1.0 + 2.0*$T1 + $C1)*$D3/6.0 + (5.0 - 2.0*$C1 + 28.0*$T1 - 3.0*$C12 + 8.0*$Ecc2Sq + 24.0*$T12)*$D5/120.0) / cos($Phi1);

  $lon = Rad2Deg($lon);

  // Create a object to store the calculated Latitude and Longitude values
  $PC_LatLon['lat'] = $lat;
  $PC_LatLon['lon'] = $lon;

  // Returns a PC_LatLon object
  return $PC_LatLon;
}
