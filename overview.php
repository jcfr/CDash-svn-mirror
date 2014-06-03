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

// handle optional date argument
@$date = $_GET["date"];
list ($previousdate, $currentstarttime, $nextdate) = get_dates($date,$Project->NightlyTime);

// begin .xml that is used to render this page
$xml = begin_XML_for_XSLT();
$xml .= get_cdash_dashboard_xml($projectname,$date);
$projectname = get_project_name($projectid);
$xml .= "<title>CDash Overview : ".$projectname."</title>";

$xml .= get_cdash_dashboard_xml_by_name($projectname, $date);

$xml .= "<menu>";
$xml .= add_XML_value("previous", "overview.php?projectid=$projectid&date=$previousdate");
$xml .= add_XML_value("current", "overview.php?projectid=$projectid");
$xml .= add_XML_value("next", "overview.phpv?projectid=$projectid&date=$nextdate");
$xml .= "</menu>";

// function to query database for relevant info
function gather_overview_data($start_date, $end_date, $build_name)
{
  global $projectid;

  $num_configure_warnings = 0;
  $num_configure_errors = 0;
  $num_build_warnings = 0;
  $num_build_errors = 0;
  $num_failing_tests = 0;
  $return_values = array();

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

  $return_values["configure warnings"] = $num_configure_warnings;
  $return_values["configure errors"] = $num_configure_errors;
  $return_values["build warnings"] = $num_build_warnings;
  $return_values["build errors"] = $num_build_errors;
  $return_values["failing tests"] = $num_failing_tests;

  return $return_values;
}

// hardcoded for now...
$build_group_names = array("linux", "mac", "windows");

// get statistics for each build group
foreach($build_group_names as $build_group_name)
  {
  $xml .= "<group>";
  $xml .= add_XML_value("name", $build_group_name);

  $beginning_timestamp = $currentstarttime;
  $end_timestamp = $currentstarttime + 3600 * 24;
  $beginning_UTCDate = gmdate(FMT_DATETIME,$beginning_timestamp);
  $end_UTCDate = gmdate(FMT_DATETIME,$end_timestamp);

  $data = gather_overview_data($beginning_UTCDate, $end_UTCDate, $build_group_name);
  $xml .= add_XML_value("configure_warnings", $data["configure warnings"]);
  $xml .= add_XML_value("configure_errors", $data["configure errors"]);
  $xml .= add_XML_value("build_warnings", $data["build warnings"]);
  $xml .= add_XML_value("build_errors", $data["build errors"]);
  $xml .= add_XML_value("failing_tests", $data["failing tests"]);

  // for charting purposes, we also pull data from the past two weeks
  $chart_configure_warnings = array();
  $chart_configure_errors = array();
  $chart_build_warnings = array();
  $chart_build_errors = array();
  $chart_failing_tests = array();
  for($i = -13; $i < 1; $i++)
    {
    $chart_beginning_timestamp = $beginning_timestamp + ($i * 3600 * 24);
    $chart_end_timestamp = $end_timestamp + ($i * 3600 * 24);
    $chart_beginning_UTCDate = gmdate(FMT_DATETIME, $chart_beginning_timestamp);
    $chart_end_UTCDate = gmdate(FMT_DATETIME, $chart_end_timestamp);
    $data = gather_overview_data($chart_beginning_UTCDate, $chart_end_UTCDate, $build_group_name);

    $chart_configure_warnings[] = array('x' => $i,
                                        'y' => $data["configure warnings"]);
    $chart_configure_errors[] = array('x' => $i,
                                      'y' => $data["configure errors"]);
    $chart_build_warnings[] = array('x' => $i, 'y' => $data["build warnings"]);
    $chart_build_errors[] = array('x' => $i, 'y' => $data["build errors"]);
    $chart_failing_tests[] = array('x' => $i, 'y' => $data["failing tests"]);
    }

  // JSON encode chart data to make it easier to use on the other end
  $xml .= add_XML_value("chart_configure_warnings",
                        json_encode($chart_configure_warnings));
  $xml .= add_XML_value("chart_configure_errors",
                        json_encode($chart_configure_errors));
  $xml .= add_XML_value("chart_build_warnings",
                        json_encode($chart_build_warnings));
  $xml .= add_XML_value("chart_build_errors",
                        json_encode($chart_build_errors));
  $xml .= add_XML_value("chart_failing_tests",
                        json_encode($chart_failing_tests));

  $xml .= "</group>";
  }

$xml .= "</cdash>";

// Now doing the xslt transition
if(!isset($NoXSLGenerate))
  {
  generate_XSLT($xml, "overview");
  }
?>
