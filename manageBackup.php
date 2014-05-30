<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: manageBackup.php 3309 2013-01-17 21:02:22Z zack.galbreath $
  Language:  PHP
  Date:      $Date: 2013-01-17 21:02:22 +0000 (Thu, 17 Jan 2013) $
  Version:   $Revision: 3309 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even 
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR 
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
include("cdash/config.php");
require_once("cdash/pdo.php");
include('login.php');
include("cdash/version.php");

if($session_OK) 
{
include_once('cdash/common.php');
include_once("cdash/ctestparser.php");

set_time_limit(0);

$db = pdo_connect("$CDASH_DB_HOST", "$CDASH_DB_LOGIN","$CDASH_DB_PASS");
pdo_select_db("$CDASH_DB_NAME",$db);

checkUserPolicy(@$_SESSION['cdash']['loginid'],0); // only admin
$xml = begin_XML_for_XSLT();
$xml .= "<title>CDash - Backup</title>";
$xml .= "<menutitle>CDash</menutitle>";
$xml .= "<menusubtitle>Backup</menusubtitle>";
$xml .= "<backurl>user.php</backurl>";
$xml .= "</cdash>";

// Now doing the xslt transition
generate_XSLT($xml,"manageBackup");

} // end session
?>
