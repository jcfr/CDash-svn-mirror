<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id$
  Language:  PHP
  Date:      $Date$
  Version:   $Revision$

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even 
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR 
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/

/*
* testSummary.php displays a list of all builds that performed a given test
* on a specific day.  It also displays information (success, execution time)
* about each copy of the test that was run.
*/
$noforcelogin = 1;
include("config.php");
require_once("pdo.php");
include('login.php');
include_once("common.php");
include("version.php"); 

$date = $_GET["date"];
if(!isset($date) || strlen($date)==0)
  { 
  die('Error: no date supplied in query string');
  }
$projectid = $_GET["project"];
if(!isset($projectid))
  {
  die('Error: no project supplied in query string');
  }
// Checks
if(!isset($projectid) || !is_numeric($projectid))
  {
  echo "Not a valid projectid!";
  return;
  }
    
$testName = $_GET["name"];
if(!isset($testName))
  {
  die('Error: no test name supplied in query string');
  }

$start = microtime_float();

$db = pdo_connect("$CDASH_DB_HOST", "$CDASH_DB_LOGIN","$CDASH_DB_PASS");
pdo_select_db("$CDASH_DB_NAME",$db);
$project = pdo_query("SELECT * FROM project WHERE id='$projectid'");
if(pdo_num_rows($project)>0)
  {
  $project_array = pdo_fetch_array($project);   
  $projectname = $project_array["name"];  
  }
  
checkUserPolicy(@$_SESSION['cdash']['loginid'],$project_array["id"]);

$xml = '<?xml version="1.0" encoding="utf-8"?><cdash>';
$xml .= "<title>CDash : ".$projectname."</title>";
$xml .= "<cssfile>".$CDASH_CSS_FILE."</cssfile>";
$xml .= "<version>".$CDASH_VERSION."</version>";

$xml .= get_cdash_dashboard_xml_by_name($projectname,$date);
$xml .="<testName>".$testName."</testName>";
  
//get information about all the builds for the given date and project
$xml .= "<builds>\n";

$testName = pdo_real_escape_string($testName);
list ($previousdate, $currentstarttime, $nextdate) = get_dates($date,$project_array["nightlytime"]);
$beginning_timestamp = $currentstarttime;
$end_timestamp = $currentstarttime+3600*24;

$beginning_UTCDate = gmdate("YmdHis",$beginning_timestamp);
$end_UTCDate = gmdate("YmdHis",$end_timestamp);                                                      

$query = "SELECT build.id,build.name,build.stamp,build2test.status,build2test.time,build2test.testid AS testid,site.name AS sitename
          FROM build
          JOIN build2test ON (build.id = build2test.buildid)
          JOIN site ON (build.siteid = site.id)
          WHERE build.projectid = '$projectid'
          AND build.starttime>=$beginning_UTCDate
          AND build.starttime<$end_UTCDate
          AND build2test.testid IN (SELECT id FROM test WHERE name='$testName')
          ORDER BY build2test.status";

$result = pdo_query($query);

$color = FALSE;
//now that we have the data we need, generate some XML
while($row = pdo_fetch_array($result))
  {
  $buildid = $row["id"];
  $xml .= "<build>\n";
 
 $xml .= add_XML_value("site", $row["sitename"]) . "\n";
  $xml .= add_XML_value("buildName", $row["name"]) . "\n";
  $xml .= add_XML_value("buildStamp", $row["stamp"]) . "\n";
  $xml .= add_XML_value("time", $row["time"]) . "\n";
  //$xml .= add_XML_value("details", $row["details"]) . "\n";
  $buildLink = "viewTest.php?buildid=$buildid";
  $xml .= add_XML_value("buildLink", $buildLink) . "\n";
  $testid = $row["testid"];
  $testLink = "testDetails.php?test=$testid&build=$buildid";
  $xml .= add_XML_value("testLink", $testLink) . "\n";
  if($color)
    {
    $xml .= add_XML_value("class", "treven") . "\n";
    }
  else
    {
    $xml .= add_XML_value("class", "trodd") . "\n";
    }
  $color = !$color;
  switch($row["status"])
    {
    case "passed":
      $xml .= add_XML_value("status", "Passed") . "\n";
      $xml .= add_XML_value("statusclass", "normal") . "\n";
      break; 
    case "failed":
      $xml .= add_XML_value("status", "Failed") . "\n";
      $xml .= add_XML_value("statusclass", "warning") . "\n";
      break;
    case "notrun":
      $xml .= add_XML_value("status", "Not Run") . "\n";
   $xml .= add_XML_value("statusclass", "error") . "\n";
      break;
    }
  $xml .= "</build>\n";
  }
$xml .= "</builds>\n";

$end = microtime_float();
$xml .= "<generationtime>".round($end-$start,3)."</generationtime>";
$xml .= "</cdash>\n";

// Now doing the xslt transition
generate_XSLT($xml,"testSummary");
?>
