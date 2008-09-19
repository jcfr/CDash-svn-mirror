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
include("config.php");
require_once("pdo.php");
include_once("common.php");
include_once("version.php"); 

$db = pdo_connect("$CDASH_DB_HOST", "$CDASH_DB_LOGIN","$CDASH_DB_PASS");
pdo_select_db("$CDASH_DB_NAME",$db);

$xml = '<?xml version="1.0"?><cdash>';
$xml .= "<title>Recover password</title>";
$xml .= "<cssfile>".$CDASH_CSS_FILE."</cssfile>";

@$recover = $_POST["recover"];
if($recover)
  {
  $email = pdo_real_escape_string($_POST["email"]);
  $emailResult = pdo_query("SELECT id FROM ".qid("user")." where email='$email'");
  add_last_sql_error("recoverPassword");
  
  if(pdo_num_rows($emailResult) == 0)
    {
    $xml .= "<warning>This email is not registered.</warning>";
    }
  else
    {
    // Create a new password
    $keychars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!#$%&";
    $length = 10;
            
    // seed with microseconds
    function make_seed_recoverpass()
      {
      list($usec, $sec) = explode(' ', microtime());
      return (float) $sec + ((float) $usec * 100000);
      }
    srand(make_seed_recoverpass());
                
    $password = "";
    $max=strlen($keychars)-1;
    for ($i=0;$i<=$length;$i++) 
      {
      $password .= substr($keychars, rand(0, $max), 1);
      }
    
    $httpprefix="http://";
    $currentPort="";
    if($_SERVER['SERVER_PORT']!=80)
      {
      $currentPort=":".$_SERVER['SERVER_PORT'];
      if($_SERVER['SERVER_PORT']!=80 )
        {
        $httpprefix = "https://";
        }
      }
    if($CDASH_USE_HTTPS === true)
      {
      $httpprefix = "https://";
      }
    $serverName = $CDASH_SERVER_NAME;
    if(strlen($serverName) == 0)
      {
      $serverName = $_SERVER['SERVER_NAME'];
      }
    
    $currentURI =  $httpprefix.$serverName.$currentPort.$_SERVER['REQUEST_URI']; 
    $currentURI = substr($currentURI,0,strrpos($currentURI,"/"));
    
    $url = $currentURI."/user.php";
    
    $text = "Hello,<br><br> You have asked to recover your password for CDash.<br><br>";
    $text .= "Your new password is: ".$password."<br>";
    $text .= "Please go to this page to login: ";
    $text .= "<a href=\"$url\">$url</a><br>";
    $text .= "<br><br>Generated by CDash";
              
    if(mail("$email","CDash password recovery", $text,
       "From: CDash <".$CDASH_EMAIL_FROM.">\nReply-To: ".$CDASH_EMAIL_REPLY."\nX-Mailer: PHP/" . phpversion()."\nMIME-Version: 1.0" ))
      {
      $md5pass = md5($password);
      // If we can send the email we update the database
      pdo_query("UPDATE ".qid("user")." SET password='$md5pass' WHERE email='$email'");
      echo pdo_error();
      add_last_sql_error("recoverPassword");
      
      $xml .= "<message>A confirmation message has been sent to your inbox.</message>";
      } 
    else 
      {
      $xml .= "<warning>Cannot send recovery email</warning>";
      }    
    }
  }

$xml .= "</cdash>";

// Now doing the xslt transition
generate_XSLT($xml,"recoverPassword");

?>
