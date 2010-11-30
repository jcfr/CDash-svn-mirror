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

// To be able to access files in this CDash installation regardless
// of getcwd() value:
//
$cdashpath = str_replace('\\', '/', dirname(dirname(__FILE__)));
set_include_path($cdashpath . PATH_SEPARATOR . get_include_path());

// Return a tree of coverage directory with the number of line covered
// and not covered
include_once('api.php');

class ProjectAPI extends CDashAPI
{
  /** Return the list of all public projects */
  private function ListProjects()
    {
    include_once('../cdash/common.php');
    $query = pdo_query("SELECT id,name FROM project WHERE public=1 ORDER BY name ASC");
    while($query_array = pdo_fetch_array($query))
      {
      $project['id'] = $query_array['id'];
      $project['name'] = $query_array['name'];
      $projects[] = $project;
      }
    return $projects;
    } // end function ListProjects

  /** 
   * Authenticate to the web API as a project admin
   * @param projectid the id of the project
   * @param key the web API key for that project
   */
  function Authenticate()
    {
    if(!isset($this->Parameters['projectid']))
      {
      return array('status'=>false, 'message'=>"You must specify a projectid parameter.");
      }
    if(!isset($this->Parameters['key']) || $this->Parameters['key'] == '')
      {
      return array('status'=>false, 'message'=>"You must specify a key parameter.");
      }

    $id = $this->Parameters['projectid'];
    $key = $this->Parameters['key'];
    $query = pdo_query("SELECT webapikey FROM project WHERE id=$id");
    if(pdo_num_rows($query) == 0)
      {
      return array('status'=>false, 'message'=>"Invalid projectid.");
      }
    $row = pdo_fetch_array($query);
    $realKey = $row['webapikey'];

    if($key != $realKey)
      {
      return array('status'=>false, 'message'=>"Incorrect API key passed.");
      }
    include_once('../cdash/common.php');
    $token = create_web_api_token($id);
    return array('status'=>true, 'token'=>$token);
    }

  /** Run function */
  function Run()
    {
    switch($this->Parameters['task'])
      {
      case 'list': return $this->ListProjects();
      case 'login': return $this->Authenticate();
      }
    }
}

?>
