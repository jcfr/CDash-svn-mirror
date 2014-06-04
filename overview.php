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
  $chart_data[$measurement] = array();
  foreach($build_group_names as $build_group_name)
    {
    $chart_data[$measurement][$build_group_name] = array();
    }
  }

// single values to display a percentage
$core_coverage = -1;
$non_core_coverage = -1;
// multiple values to display as a chart
$core_coverage_data = array();
$non_core_coverage_data = array();
// data for bullet charts
$core_coverage_min = 101;
$core_coverage_max = -1;
$core_coverage_avg = 0;
$core_coverage_prev = -1;
$non_core_coverage_min = 101;
$non_core_coverage_max = -1;
$non_core_coverage_avg = 0;
$non_core_coverage_prev = -1;

// gather up the relevant stats
foreach($build_group_names as $build_group_name)
  {
  $beginning_timestamp = $currentstarttime;
  $end_timestamp = $currentstarttime + 3600 * 24;
  $beginning_UTCDate = gmdate(FMT_DATETIME,$beginning_timestamp);
  $end_UTCDate = gmdate(FMT_DATETIME,$end_timestamp);

  $data = gather_overview_data($beginning_UTCDate, $end_UTCDate, $build_group_name);

  // for now, we assume that coverage will only be performed by one
  // of these build groups...
  if (array_key_exists("core_coverage", $data))
    {
    $core_coverage = $data["core_coverage"];
    }
  if (array_key_exists("non_core_coverage", $data))
    {
    $non_core_coverage = $data["non_core_coverage"];
    }

  foreach($measurements as $measurement)
    {
    $overview_data[$measurement][$build_group_name] = $data[$measurement];
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
      $chart_data[$measurement][$build_group_name][] = array('x' => $i, 'y' => $data[$measurement]);
      }
    // coverage too
    if (array_key_exists("core_coverage", $data))
      {
      $core_coverage_data[] = array('x' => $i, 'y' => $data["core_coverage"]);
      if ($data["core_coverage"] < $core_coverage_min)
        {
        $core_coverage_min = $data["core_coverage"];
        }
      if ($data["core_coverage"] > $core_coverage_max)
        {
        $core_coverage_max = $data["core_coverage"];
        }
      $core_coverage_avg += $data["core_coverage"];
      $core_coverage_prev = $data["core_coverage"];
      }
    if (array_key_exists("non_core_coverage", $data))
      {
      $non_core_coverage_data[] = array('x' => $i, 'y' => $data["non_core_coverage"]);
      if ($data["non_core_coverage"] < $non_core_coverage_min)
        {
        $non_core_coverage_min = $data["non_core_coverage"];
        }
      if ($data["non_core_coverage"] > $non_core_coverage_max)
        {
        $non_core_coverage_max = $data["non_core_coverage"];
        }
      $non_core_coverage_avg += $data["non_core_coverage"];
      }
    }
  }

if ($core_coverage_avg != 0)
  {
  $core_coverage_avg /= count($core_coverage_data);
  }
if ($non_core_coverage_avg != 0)
  {
  $non_core_coverage_avg /= count($non_core_coverage_data);
  }
if (count($core_coverage_data) > 1)
  {
  $core_coverage_prev = $core_coverage_data[count($core_coverage_data) - 2]['y'];
  }
else
  {
  $core_coverage_prev = end($core_coverage_data)['y'];
  }
if (count($non_core_coverage_data) > 1)
  {
  $non_core_coverage_prev = $non_core_coverage_data[count($non_core_coverage_data) - 2]['y'];
  }
else
  {
  $non_core_coverage_prev = end($non_core_coverage_data)['y'];
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
    // JSON encode chart data to make it easier to use on the other end
    $xml .= add_XML_value("chart", json_encode($chart_data[$measurement][$build_group_name]));
    $xml .= "</group>";
    }
  $xml .= "</measurement>";
  }

if ($core_coverage != -1)
  {
  $xml .= "<coverage>";
  $xml .= add_XML_value("value", "$core_coverage");
  $xml .= add_XML_value("previous", "$core_coverage_prev");
  $xml .= add_XML_value("min", "$core_coverage_min");
  $xml .= add_XML_value("avg", "$core_coverage_avg");
  $xml .= add_XML_value("max", "$core_coverage_max");
  $xml .= add_XML_value("chart", json_encode($core_coverage_data));
  $xml .= "</coverage>";
  }
if ($non_core_coverage != -1)
  {
  $xml .= "<non_core_coverage>";
  $xml .= add_XML_value("value", "$non_core_coverage");
  $xml .= add_XML_value("previous", "$non_core_coverage_prev");
  $xml .= add_XML_value("min", "$non_core_coverage_min");
  $xml .= add_XML_value("avg", "$non_core_coverage_avg");
  $xml .= add_XML_value("max", "$non_core_coverage_max");
  $xml .= add_XML_value("chart", json_encode($non_core_coverage_data));
  $xml .= "</non_core_coverage>";
  }

$xml .= "</cdash>";

// Now do the xslt transition
if(!isset($NoXSLGenerate))
  {
  generate_XSLT($xml, "overview");
  }
?>
