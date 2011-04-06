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
require_once("cdash/pdo.php");
include_once('cdash/common.php');
include("cdash/version.php");

include('login.php');

include_once('models/project.php');
include_once('models/build.php');
include_once('models/site.php');
include_once('models/uploadfile.php');

if(!isset($_GET['buildid']))
  {
  echo "Build id not set";
  return;
  }

$buildid = $_GET['buildid'];
$Build = new Build();
$Build->Id = $buildid;
$Build->FillFromId($buildid);
$Site = new Site();
$Site->Id = $Build->SiteId;

$build_array = pdo_fetch_array(pdo_query("SELECT projectid FROM build WHERE id='$buildid'"));  
$projectid = $build_array["projectid"];
checkUserPolicy(@$_SESSION['cdash']['loginid'],$projectid);

$xml = '<?xml version="1.0"?><cdash>';
$xml .= add_XML_value("cssfile",$CDASH_CSS_FILE);
$xml .= add_XML_value("version",$CDASH_VERSION);
$xml .= get_cdash_dashboard_xml(get_project_name($projectid),$date);

$db = pdo_connect("$CDASH_DB_HOST", "$CDASH_DB_LOGIN","$CDASH_DB_PASS");
pdo_select_db("$CDASH_DB_NAME",$db);
$xml .= add_XML_value("title","CDash - Uploaded files");
$xml .= add_XML_value("menutitle","CDash");
$xml .= add_XML_value("menusubtitle","Uploaded files");

$xml .= "<hostname>".$_SERVER['SERVER_NAME']."</hostname>";
$xml .= "<date>".date("r")."</date>";
$xml .= "<backurl>index.php</backurl>";

$xml .= "<buildid>$buildid</buildid>";
$xml .= '<buildname>'.$Build->Name.'</buildname>';
$xml .= '<buildstarttime>'.$Build->StartTime.'</buildstarttime>';
$xml .= '<siteid>'.$Site->Id.'</siteid>';
$xml .= '<sitename>'.$Site->GetName().'</sitename>';

$uploadFiles = $Build->GetUploadedFiles();

foreach($uploadFiles as $uploadFile)
  {
  $xml .= '<uploadfile>';
  $xml .= '<id>'.$uploadFile->Id.'</id>';
  $xml .= '<href>'.$CDASH_UPLOAD_DIRECTORY.'/'.$uploadFile->Sha1Sum.'/'.$uploadFile->Filename.'</href>';
  $xml .= '<sha1sum>'.$uploadFile->Sha1Sum.'</sha1sum>';
  $xml .= '<filename>'.$uploadFile->Filename.'</filename>';
  $xml .= '<filesize>'.$uploadFile->Filesize.'</filesize>';
  $xml .= '</uploadfile>';
  }

$xml .= "</cdash>";
generate_XSLT($xml, "viewFiles", true);
?>
