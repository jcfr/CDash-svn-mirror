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
$noforcelogin = 1;
include("config.php");
require_once("pdo.php");
include('login.php');
include_once("common.php");
include("version.php");

@$buildid = $_GET["buildid"];
@$date = $_GET["date"];

// Checks
if(!isset($buildid) || !is_numeric($buildid))
  {
  echo "Not a valid buildid!";
  return;
  }
 
$db = pdo_connect("$CDASH_DB_HOST", "$CDASH_DB_LOGIN","$CDASH_DB_PASS");
pdo_select_db("$CDASH_DB_NAME",$db);
  
$build_array = pdo_fetch_array(pdo_query("SELECT * FROM build WHERE id='$buildid'"));  
$projectid = $build_array["projectid"];
checkUserPolicy(@$_SESSION['cdash']['loginid'],$projectid);

$date = date("Y-m-d", strtotime($build_array["starttime"]));
    
$project = pdo_query("SELECT * FROM project WHERE id='$projectid'");
if(pdo_num_rows($project)>0)
  {
  $project_array = pdo_fetch_array($project);
  $svnurl = $project_array["cvsurl"];
  $projectname = $project_array["name"];  
  }

$xml = '<?xml version="1.0"?><cdash>';
$xml .= "<title>CDash : ".$projectname."</title>";
$xml .= "<cssfile>".$CDASH_CSS_FILE."</cssfile>";
$xml .= "<version>".$CDASH_VERSION."</version>";

$xml .= get_cdash_dashboard_xml_by_name($projectname,$date);

  // Build
  $xml .= "<build>";
  $build = pdo_query("SELECT * FROM build WHERE id='$buildid'");
  $build_array = pdo_fetch_array($build); 
  $siteid = $build_array["siteid"];
  $site_array = pdo_fetch_array(pdo_query("SELECT name FROM site WHERE id='$siteid'"));
  $xml .= add_XML_value("site",$site_array["name"]);
  $xml .= add_XML_value("buildname",$build_array["name"]);
  $xml .= add_XML_value("buildid",$build_array["id"]);
  $xml .= add_XML_value("buildtime",date("D, d M Y H:i:s T",strtotime($build_array["starttime"]." UTC")));  
   
  $xml .= "</build>";

  $xml .= "<updates>";

  
 // Return the status
  $status_array = pdo_fetch_array(pdo_query("SELECT status FROM buildupdate WHERE buildid='$buildid'"));
 if(strlen($status_array["status"]) > 0 && $status_array["status"]!="0")
  {
    $xml .= add_XML_value("status",$status_array["status"]);
  }
  else
  {
  $xml .= add_XML_value("status",""); // empty status
  }

  $xml .= "<javascript>";
  
 // This should work hopefully
  $updatedfiles = pdo_query("SELECT * FROM updatefile WHERE buildid='$buildid'
                              ORDER BY REVERSE(RIGHT(REVERSE(filename),LOCATE('/',REVERSE(filename)))) ");
  
  function sort_array_by_directory($a,$b)
    { 
    return $a>$b ? 1:0;
    }
    
  function sort_array_by_filename($a,$b)
    {
    // Extract directory
    $filenamea = $a['filename'];
    $filenameb = $b['filename'];  
    return $filenamea>$filenameb ? 1:0;
    }
    
  $directoryarray = array();
  $updatearray1 = array();
  // Create an array so we can sort it
  while($file_array = pdo_fetch_array($updatedfiles))
    {
    $file = array();
    $file['filename'] = $file_array["filename"];
    $file['author'] = $file_array["author"];
    $file['email'] = get_author_email($projectname, $file['author']);
    $file['log'] = $file_array["log"];
    $file['revision'] = $file_array["revision"];
    $updatearray1[] = $file;
    $directoryarray[] = substr($file_array["filename"],0,strrpos($file_array["filename"],"/"));
    }
  
  $directoryarray = array_unique($directoryarray);
  usort($directoryarray, "sort_array_by_directory");
  usort($updatearray1, "sort_array_by_filename");
  
  $updatearray = array();
  
  foreach($directoryarray as $directory)
    {
    foreach($updatearray1 as $update)
      {
      $filename = $update['filename'];
      if(substr($filename,0,strrpos($filename,"/")) == $directory)
        {
        $updatearray[] = $update;
        }
      }
    }
  
  //$xml .= "dbAdd (true, \"".$projectname." Updated files  (".pdo_num_rows($updatedfiles).")\", \"\", 0, \"\", \"1\", \"\", \"\", \"\")\n";
  
  //$previousdir = "";
  $projecturl = $svnurl;
  
  $locallymodified = array();
  $conflictingfiles = array();
  $updatedfiles = array();
  
  // locally cached query result same as get_project_property($projectname, "cvsurl");
  foreach($updatearray as $file)
    {
    $filename = $file['filename'];
    $filename = str_replace("\\", "/", $filename);
    $directory = substr($filename,0,strrpos($filename,"/"));
     
    $pos = strrpos($filename,"/");
    if($pos !== FALSE)
      { 
      $filename = substr($filename,$pos+1);
      }
 
      
    $author = $file['author'];
    $email = $file['email'];
    $log = $file['log'];
    $revision = $file['revision'];
    $log = str_replace("\r"," ",$log);
    $log = str_replace("\n", " ", $log);
    // Do this twice so that <something> ends up as
    // &amp;lt;something&amp;gt; because it gets sent to a 
    // java script function not just displayed as html
    $log = XMLStrFormat($log);
    $log = XMLStrFormat($log);
    $log = trim($log);
    
    $file['directory'] = $directory;
    $file['author'] = $author;
    $file['email'] = $email;
    $file['log'] = $log;        
    $file['revision'] = $revision;    
    $file['filename'] = $filename;
    $file['bugurl'] = ""; 
    // If the log starts with BUG:
    if(strpos($log,"BUG:") !== FALSE && strpos($log,"BUG:")==0)
      {
      // Try to find the bugid
      $posend = strpos($log," ",6);
      if($posend === FALSE)
        {
        $posend = strlen($log);
        }
      $bugid = trim(substr($log,4,$posend-4));      
      if(is_numeric($bugid))
        {
        // For now we assume we are using mantis in the future we might want 
        // to support other bug trackers
        $slash = "";
        $url = $project_array["bugtrackerurl"];
        if($url[strlen($url)-1] != "/")
          {
          $slash = "/";
          }
        $file['bugurl'] = "http://".$project_array["bugtrackerurl"].$slash."view.php?id=".$bugid;
        } // end have bugid
      else
        {
        $file['bugurl'] = "http://".$project_array["bugtrackerurl"];
        }
      }
     
    if($revision != "-1" && $log!="Locally modified file")
      {
      $diff_url = get_diff_url($projectid,$projecturl, $directory, $filename, $revision);
      $diff_url = XMLStrFormat($diff_url);
      $file['diff_url'] = $diff_url;  
      $updatedfiles[] = $file;
      }
    else if(strstr($log,"Locally modified file"))
      {
      $diff_url = get_diff_url($projectid,$projecturl, $directory, $filename);
      $diff_url = XMLStrFormat($diff_url);
      $file['diff_url'] = $diff_url;  
      $locallymodified[] = $file;
      }
    else if(strstr($log,"Conflict while updating"))
      {
      $diff_url = get_diff_url($projectid,$projecturl, $directory, $filename);
      $diff_url = XMLStrFormat($diff_url);
      $file['diff_url'] = $diff_url;  
      $conflictingfiles[] = $file;
      }
      
    }
  
  // Updated files
  $xml .= "dbAdd (true, \"".$projectname." Updated files  (".count($updatedfiles).")\", \"\", 0, \"\", \"1\", \"\", \"\", \"\",\"\")\n";
   $previousdir = "";
  foreach($updatedfiles as $file)
    {
    $directory = $file['directory'];
    if($previousdir=="" || $directory != $previousdir)
      {
      $xml .= " dbAdd (true, \"".$directory."\", \"\", 1, \"\", \"1\", \"\", \"\", \"\",\"\")\n";
      $previousdir = $directory;
      }
    $xml .= " dbAdd ( false, \"".$file['filename']." Revision: ".$file['revision']."\",\"".$file['diff_url']."\",2,\"\",\"1\",\"".$file['author']."\",\"".$file['email']."\",\"".$file['log']."\",\"".$file['bugurl']."\")\n";
    }

  // Modified files
  $xml .= "dbAdd (true, \"Modified files  (".count($locallymodified).")\", \"\", 0, \"\", \"1\", \"\", \"\", \"\",\"\")\n";
  $previousdir = "";
  foreach($locallymodified as $file)
    {
    $directory = $file['$directory'];
    if($previousdir=="" || $directory != $previousdir)
      {
      $xml .= " dbAdd (true, \"".$directory."\", \"\", 1, \"\", \"1\", \"\", \"\", \"\",\"\")\n";
      $previousdir = $directory;
      }
    $xml .= " dbAdd ( false, \"".$file['filename']."\",\"".$file['diff_url']."\",2,\"\",\"1\",\"".$file['author']."\",\"".$file['email']."\",\"".$file['log']."\",\"".$file['bugurl']."\")\n";
    }
  
  // Conflicting files
  $xml .= "dbAdd (true, \"Conflicting files  (".count($conflictingfiles).")\", \"\", 0, \"\", \"1\", \"\", \"\", \"\",\"\")\n";
  $previousdir = "";
  foreach($conflictingfiles as $file)
    {
    $directory = $file['$directory'];
    if($previousdir=="" || $directory != $previousdir)
      {
      $xml .= " dbAdd (true, \"".$directory."\", \"\", 1, \"\", \"1\", \"\", \"\", \"\")\n";
      $previousdir = $directory;
      }
    $xml .= " dbAdd ( false, \"".$file['$filename']." Revision: ".$file['$revision']."\",\"".$file['$diff_url']."\",2,\"\",\"1\",\"".$file['$author']."\",\"".$file['$email']."\",\"".$file['$log']."\",\"".$file['$bugurl']."\")\n";
    }

  $xml .= "</javascript>";
  $xml .= "</updates>";
  $xml .= "</cdash>";
 

// Now doing the xslt transition
generate_XSLT($xml,"viewUpdate");
?>
