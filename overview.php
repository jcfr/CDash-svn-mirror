<?php
/*=========================================================================

  Copyright (c) Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/

$noforcelogin = 1;
include("cdash/config.php");
require_once("cdash/pdo.php");
include('login.php');
include_once("cdash/common.php");
include("cdash/version.php");

// handle required projectid argument
@$projectid = $_GET["projectid"];
// Checks
if(!isset($projectid) || !is_numeric($projectid))
  {
  echo "Not a valid projectid!";
  return;
  }

// make sure the user has access to this project
checkUserPolicy(@$_SESSION['cdash']['loginid'], $projectid);

// connect to the database
$db = pdo_connect("$CDASH_DB_HOST", "$CDASH_DB_LOGIN","$CDASH_DB_PASS");
pdo_select_db("$CDASH_DB_NAME",$db);

// begin .xml that is used to render this page
$xml = begin_XML_for_XSLT();
$xml .= get_cdash_dashboard_xml($projectname,$date);
$projectname = get_project_name($projectid);
$xml .= "<title>CDash Overview : ".$projectname."</title>";


// function to query database for relevant info
function generate_overview_xml($start_date, $end_date, $build_name)
{
  global $projectid;
  global $xml;
  global $num_configure_errors;
  global $num_configure_warnings;
  global $num_build_errors;
  global $num_build_warnings;
  global $num_failing_tests;

  $builds_query = "SELECT b.id, b.builderrors, b.buildwarnings, b.testfailed,
                   c.status AS configurestatus, c.warnings AS configurewarnings
                   FROM build AS b
                   LEFT JOIN configure AS c ON (c.buildid=b.id)
                   WHERE b.projectid = '$projectid'
                   AND b.starttime < '$end_date'
                   AND b.starttime >= '$start_date'
                   AND b.`name` LIKE '%$build_name%'";

  $builds_array = pdo_query($builds_query);
  while($build_row = pdo_fetch_array($builds_array))
    {
    if ($build_row["configurewarnings"] > 0)
      {
      $num_configure_warnings += $build_row["configurewarnings"];
      }

    if ($build_row["configurestatus"] != 0)
      {
      $num_configure_errors++;
      }

    if ($build_row["buildwarnings"] > 0)
      {
      $num_build_warnings += $build_row["buildwarnings"];
      }

    if ($build_row["builderrors"] > 0)
      {
      $num_build_errors += $build_row["builderrors"];
      }

    if ($build_row["testfailed"] > 0)
      {
      $num_failings_tests += $build_row["testfailed"];
      }
    }

  /* JSON instead for d3 perhaps...
  $xml .= "<date>";
  $xml .= add_XML_value("configureerrors", $num_configure_errors);
  $xml .= add_XML_value("configurewarnings", $num_configure_warnings);
  $xml .= add_XML_value("builderrors", $num_build_errors);
  $xml .= add_XML_value("buildwarnings", $num_build_warnings);
  $xml .= add_XML_value("failingtests", $num_failing_tests);
  $xml .= "</date>";
  */
}


// handle optional date argument
@$date = $_GET["date"];
list ($previousdate, $currentstarttime, $nextdate) = get_dates($date,$Project->NightlyTime);
$beginning_timestamp = $currentstarttime;
$end_timestamp = $currentstarttime+3600*24;
$beginning_UTCDate = gmdate(FMT_DATETIME,$beginning_timestamp);
$end_UTCDate = gmdate(FMT_DATETIME,$end_timestamp);

$build_group_names = array ("linux", "mac", "windows");

// get statistics for each build group 
foreach($build_group_names as $build_group_name)
  {
  $xml .= "<group>";
  $xml .= add_XML_value("name", $build_group_name);

  $num_configure_errors = 0;
  $num_configure_warnings = 0;
  $num_build_errors = 0;
  $num_build_warnings = 0;
  $num_failing_tests = 0;
  generate_overview_xml($beginning_UTCDate, $end_UTCDate, $build_group_name);
  $xml .= add_XML_value("configure_warnings", $num_configure_warnings);
  $xml .= add_XML_value("configure_errors", $num_configure_errors);
  $xml .= add_XML_value("build_warnings", $num_build_warnings);
  $xml .= add_XML_value("build_errors", $num_build_errors);
  $xml .= add_XML_value("failing_tests", $num_failing_tests);

/*
  // for charting purposes, we also pull data from the past two weeks
  for($i = 0; $i < 14; $i++)
  {
    $beginning_timestamp -= 3600 * 24;
    $end_timestamp -= 3600 * 24;
    $beginning_UTCDate = gmdate(FMT_DATETIME,$beginning_timestamp);
    $end_UTCDate = gmdate(FMT_DATETIME,$end_timestamp);
  }
*/

  $xml .= "</group>";
  }

$xml .= "</cdash>";

file_put_contents("/tmp/zackdebug.xml", $xml);

// Now doing the xslt transition
if(!isset($NoXSLGenerate))
  {
  generate_XSLT($xml, "overview");
  }
?>
