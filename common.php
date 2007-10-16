<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $RCSfile: common.php,v $
  Language:  PHP
  Date:      $Date$
  Version:   $Revision$

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even 
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR 
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
function add_XML_value($tag,$value)
{
  return "<".$tag.">".$value."</".$tag.">";
}

/** Get the build id from stamp, name and buildname */
function get_build_id($buildname,$stamp)
{
		include("config.php");

		$db = mysql_connect("$CDASH_DB_HOST", "$CDASH_DB_LOGIN","$CDASH_DB_PASS");
  mysql_select_db("$CDASH_DB_NAME",$db);

  $build = mysql_query("SELECT id FROM build WHERE name='$buildname' AND stamp='$stamp'");
		if(mysql_num_rows($build)>0)
		  {
				$build_array = mysql_fetch_array($build);
				return $build_array["id"];
    }
				
		return -1;
}

/** Get the project id from the project name */
function get_project_id($projectname)
{
		include("config.php");

		$db = mysql_connect("$CDASH_DB_HOST", "$CDASH_DB_LOGIN","$CDASH_DB_PASS");
  mysql_select_db("$CDASH_DB_NAME",$db);

  $project = mysql_query("SELECT id FROM project WHERE name='$projectname'");
		if(mysql_num_rows($project)>0)
		  {
				$project_array = mysql_fetch_array($project);
				return $project_array["id"];
    }
				
		return -1;
}

/** Create a site */
function add_site($name,$description="",$processor="",$numprocessors="1",$ip="")
{
		include("config.php");
	 $db = mysql_connect("$CDASH_DB_HOST", "$CDASH_DB_LOGIN","$CDASH_DB_PASS");
  mysql_select_db("$CDASH_DB_NAME",$db);

  // Check if we already have the site registered
  $site = mysql_query("SELECT id FROM site WHERE name='$name'");
		if(mysql_num_rows($site)>0)
		  {
				$site_array = mysql_fetch_array($site);
				return $site_array["id"];
		  }
		
		// If not found we create the site
		// Should compute the location from IP
		$latitude = "";
		$longitude = "";
				
		mysql_query ("INSERT INTO site (name,description,processor,numprocessors,ip,latitude,longitude) 
		                        VALUES ('$name','$description','$processor','$numprocessors','$ip','$latitude','$longitude')");
		echo mysql_error();		
	 return mysql_insert_id();
}

/** Add a new build */
function add_build($projectid,$siteid,$name,$stamp,$type,$generator,$starttime,$endtime,$command,$log)
{
  mysql_query ("INSERT INTO build (projectid,siteid,name,stamp,type,generator,starttime,endtime,command,log) 
		                        VALUES ('$projectid','$siteid','$name','$stamp','$type','$generator',
																										        '$starttime','$endtime','$command','$log')");
		echo mysql_error();		
	 return mysql_insert_id();
}

/** Add a new configure */
function add_configure($buildid,$starttime,$endtime,$command,$log,$status)
{
  $command = addslashes($command);
	 $log = addslashes($log);
				
  mysql_query ("INSERT INTO configure (buildid,starttime,endtime,command,log,status) 
		             VALUES ('$buildid','$starttime','$endtime','$command','$log','$status')");
		echo mysql_error();
}

/** Add a new test */
function add_test($buildid,$name,$status,$path,$fullname,$command)
{
  $command = addslashes($command);
				
  mysql_query ("INSERT INTO test (buildid,name,status,path,fullname,command) 
		             VALUES ('$buildid','$name','$status','$path','$fullname','$command')");
		echo mysql_error();
}

/** Add a new error/warning */
function 	add_error($buildid,$type,$logline,$text,$sourcefile,$sourceline,$precontext,$postcontext,$repeatcount)
{
  $text = addslashes($text);
		$precontext = addslashes($precontext);
		$postcontext = addslashes($postcontext);
				
  mysql_query ("INSERT INTO builderror (buildid,type,logline,text,sourcefile,sourceline,precontext,postcontext,repeatcount) 
		             VALUES ('$buildid','$type','$logline','$text','$sourcefile','$sourceline','$precontext','$postcontext','$repeatcount')");
		echo mysql_error();
}

/** Add a new update */
function		add_update($buildid,$start_time,$end_time,$command,$type)
{
  $command = addslashes($command);
				
  mysql_query ("INSERT INTO buildupdate (buildid,starttime,endtime,command,type) 
		             VALUES ('$buildid','$start_time','$end_time','$command','$type')");
		echo mysql_error();
}

/** Add a new update file */
function	add_updatefile($buildid,$filename,$checkindate,$author,$email,$log,$revision,$priorrevision)
{
  $log = addslashes($log);
				
  mysql_query ("INSERT INTO updatefile (buildid,filename,checkindate,author,email,log,revision,priorrevision) 
		             VALUES ('$buildid','$filename','$checkindate','$author','$email','$log','$revision','$priorrevision')");
		echo mysql_error();
}

/** Add a new note */
function	add_note($buildid,$text)
{
  $text = addslashes($text);
				
  mysql_query ("INSERT INTO note (buildid,text) VALUES ('$buildid','$text')");
		echo mysql_error();
}

/**
 * Recursive version of glob
 *
 * @return array containing all pattern-matched files.
 *
 * @param string $sDir      Directory to start with.
 * @param string $sPattern  Pattern to glob for.
 * @param int $nFlags       Flags sent to glob.
 */
function globr($sDir, $sPattern, $nFlags = NULL)
{
  $sDir = escapeshellcmd($sDir);

  // Get the list of all matching files currently in the
  // directory.

  $aFiles = glob("$sDir/$sPattern", $nFlags);

  // Then get a list of all directories in this directory, and
  // run ourselves on the resulting array.  This is the
  // recursion step, which will not execute if there are no
  // directories.

  foreach (glob("$sDir/*", GLOB_ONLYDIR) as $sSubDir)
    {
    $aSubFiles = globr($sSubDir, $sPattern, $nFlags);
    $aFiles = array_merge($aFiles, $aSubFiles);
    }

  // The array we return contains the files we found, and the
  // files all of our children found.

  return $aFiles;
} 

?>
