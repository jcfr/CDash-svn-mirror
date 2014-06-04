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
require_once("models/project.php");

// handle required project argument
@$projectname = $_GET["project"];
if(!isset($projectname))
  {
  echo "Not a valid projectid!";
  return;
  }
$projectname = htmlspecialchars(pdo_real_escape_string($projectname));
$projectid = get_project_id($projectname);
$Project = new Project();
$Project->Id = $projectid;
$Project->Fill();

// make sure the user has access to this project
checkUserPolicy(@$_SESSION['cdash']['loginid'], $projectid);

// connect to the database
$db = pdo_connect("$CDASH_DB_HOST", "$CDASH_DB_LOGIN","$CDASH_DB_PASS");
pdo_select_db("$CDASH_DB_NAME",$db);

// handle optional date argument
@$date = $_GET["date"];
if ($date != NULL)
  {
  $date = htmlspecialchars(pdo_real_escape_string($date));
  }
list ($previousdate, $currentstarttime, $nextdate) = get_dates($date, $Project->NightlyTime);

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

  // for coverage
  $core_tested = 0;
  $core_untested = 0;
  $non_core_tested = 0;
  $non_core_untested = 0;

  $builds_query = "SELECT b.id, b.builderrors, b.buildwarnings, b.testfailed,
                   c.status AS configurestatus, c.warnings AS configurewarnings,
                   cs.loctested AS loctested, cs.locuntested AS locuntested,
                   sp.id AS subprojectid, sp.core AS subprojectcore
                   FROM build AS b
                   LEFT JOIN configure AS c ON (c.buildid=b.id)
                   LEFT JOIN coveragesummary AS cs ON (cs.buildid=b.id)
                   LEFT JOIN subproject2build AS sp2b ON (sp2b.buildid = b.id)
                   LEFT JOIN subproject as sp ON (sp2b.subprojectid = sp.id)
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
      $num_failing_tests += $build_row["testfailed"];
      }

    if ($build_row["subprojectcore"] == 0)
      {
      $non_core_tested += $build_row["loctested"];
      $non_core_untested += $build_row["locuntested"];
      }
    else if ($build_row["subprojectcore"] == 1)
      {
      $core_tested += $build_row["loctested"];
      $core_untested += $build_row["locuntested"];
      }
    }

  $return_values["configure_warnings"] = $num_configure_warnings;
  $return_values["configure_errors"] = $num_configure_errors;
  $return_values["build_warnings"] = $num_build_warnings;
  $return_values["build_errors"] = $num_build_errors;
  $return_values["failing_tests"] = $num_failing_tests;

  if ($core_tested + $core_untested > 0)
    {
    $return_values["core_coverage"] =
      round($core_tested / ($core_tested + $core_untested) * 100, 2);
    }
  if ($non_core_tested + $non_core_untested > 0)
    {
    $return_values["non_core_coverage"] =
      round($non_core_tested / ($non_core_tested + $non_core_untested) * 100, 2);
    }

  return $return_values;
}


$measurements = array("configure_warnings", "configure_errors",
                      "build_warnings", "build_errors", "failing_tests");

// hardcoded for now...
$build_group_names = array("linux", "peanut", "yellowstone");

$overview_data = array();
foreach($measurements as $measurement)
  {
  $overview_data[$measurement] = array();
  $linechart_data[$measurement] = array();
  foreach($build_group_names as $build_group_name)
    {
    $linechart_data[$measurement][$build_group_name] = array();
    }
  }

$coverage_data = array();
// also hardcoded for now
$coverage_group_names = array("core", "non_core");
foreach($coverage_group_names as $coverage_group_name)
  {
  $coverage_data[$coverage_group_name] = array();
  $linechart_data[$coverage_group_name . "_coverage"] = array();

  $coverage_data[$coverage_group_name]["min"] = 101;
  $coverage_data[$coverage_group_name]["max"] = -1;
  $coverage_data[$coverage_group_name]["average"] = 0;
  $coverage_data[$coverage_group_name]["previous"] = 0;
  $coverage_data[$coverage_group_name]["current"] = 0;
  }

// gather up the relevant stats
foreach($build_group_names as $build_group_name)
  {
  $beginning_timestamp = $currentstarttime;
  $end_timestamp = $currentstarttime + 3600 * 24;
  $beginning_UTCDate = gmdate(FMT_DATETIME,$beginning_timestamp);
  $end_UTCDate = gmdate(FMT_DATETIME,$end_timestamp);

  $data = gather_overview_data($beginning_UTCDate, $end_UTCDate, $build_group_name);

  foreach($measurements as $measurement)
    {
    $overview_data[$measurement][$build_group_name] = $data[$measurement];
    }

  // here we assume that coverage will only be performed by one
  // of the build groups, otherwise this data will be overwritten each
  // time through this (outer) foreach loop.
  foreach($coverage_group_names as $coverage_group_name)
    {
    $key_name = $coverage_group_name . "_coverage";
    if (array_key_exists($key_name, $data))
      {
      $coverage_data[$coverage_group_name]["current"] = $data[$key_name];
      }
    }

  // for charting purposes, we also pull data from the past two weeks
  for($i = -13; $i < 1; $i++)
    {
    $chart_beginning_timestamp = $beginning_timestamp + ($i * 3600 * 24);
    $chart_end_timestamp = $end_timestamp + ($i * 3600 * 24);
    $chart_beginning_UTCDate = gmdate(FMT_DATETIME, $chart_beginning_timestamp);
    $chart_end_UTCDate = gmdate(FMT_DATETIME, $chart_end_timestamp);
    $data = gather_overview_data($chart_beginning_UTCDate, $chart_end_UTCDate, $build_group_name);
    foreach($measurements as $measurement)
      {
      $linechart_data[$measurement][$build_group_name][] = array('x' => $i, 'y' => $data[$measurement]);
      }

    // coverage too
    foreach($coverage_group_names as $coverage_group_name)
      {
      $key_name = $coverage_group_name . "_coverage";
      if (array_key_exists($key_name, $data))
        {
        $coverage_value = $data[$key_name];

        $linechart_data[$key_name][] = array('x' => $i, 'y' => $coverage_value);

        if ($coverage_value < $coverage_data[$coverage_group_name]["min"])
          {
          $coverage_data[$coverage_group_name]["min"] = $coverage_value;
          }
        if ($coverage_value > $coverage_data[$coverage_group_name]["max"])
          {
          $coverage_data[$coverage_group_name]["max"] = $coverage_value;
          }
        $coverage_data[$coverage_group_name]["average"] += $coverage_value;
        }
      }
    }
  }

// compute average & previous coverage values
foreach($coverage_group_names as $coverage_group_name)
  {
  $key_name = $coverage_group_name . "_coverage";

  // divide our running average by the number of data points we encountered
  if ($coverage_data[$coverage_group_name]["average"] != 0)
    {
    $coverage_data[$coverage_group_name]["average"] /=
      count($linechart_data[$key_name]);
    }

  // isolate the previous coverage value.  This is typically the
  // second to last coverage data point that we collected, but
  // we're careful to check for the case where only a single point
  // was recovered.
  $num_points = count($linechart_data[$key_name]);
  if ($num_points > 1)
    {
    $coverage_data[$coverage_group_name]["previous"] =
      $linechart_data[$key_name][$num_points - 2]['y'];
    }
  else
    {
    $coverage_data[$coverage_group_name]["previous"] =
      end($linechart_data[$key_name])['y'];
    }
  }

// now that the data has been collected, we can generate the .xml data
foreach($build_group_names as $build_group_name)
  {
  $xml .= "<group>";
  $xml .= add_XML_value("name", $build_group_name);
  $xml .= "</group>";
  }
foreach($measurements as $measurement)
  {
  $xml .= "<measurement>";
  $xml .= add_XML_value("name", $measurement);
  $xml .= add_XML_value("nice_name", str_replace("_", " ", $measurement));
  foreach($build_group_names as $build_group_name)
    {
    $xml .= "<group>";
    $xml .= add_XML_value("group_name", $build_group_name);
    $xml .= add_XML_value("value", $overview_data[$measurement][$build_group_name]);
    // JSON encode linechart data to make it easier to use on the client side
    $xml .= add_XML_value("chart", json_encode($linechart_data[$measurement][$build_group_name]));
    $xml .= "</group>";
    }
  $xml .= "</measurement>";
  }

foreach($coverage_group_names as $coverage_group_name)
  {
  $xml .= "<coverage>";
  $xml .= add_XML_value("name", "$coverage_group_name");
  $xml .= add_XML_value("nice_name", str_replace("_", " ", $coverage_group_name));
  $xml .= add_XML_value("min", $coverage_data[$coverage_group_name]["min"]);
  $xml .= add_XML_value("max", $coverage_data[$coverage_group_name]["max"]);
  $xml .= add_XML_value("average",
    $coverage_data[$coverage_group_name]["average"]);
  $xml .= add_XML_value("current",
    $coverage_data[$coverage_group_name]["current"]);
  $xml .= add_XML_value("previous",
    $coverage_data[$coverage_group_name]["previous"]);
  $xml .= add_XML_value("chart",
    json_encode($linechart_data[$coverage_group_name . "_coverage"]));
  $xml .= "</coverage>";
  }

$xml .= "</cdash>";

// Now do the xslt transition
if(!isset($NoXSLGenerate))
  {
  generate_XSLT($xml, "overview");
  }
?>
