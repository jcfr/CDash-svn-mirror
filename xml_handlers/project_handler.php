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
require_once 'xml_handlers/abstract_handler.php';
require_once('models/project.php');
require_once('models/subproject.php');

class ProjectHandler extends AbstractHandler
{
  private $SubProject;
  private $Dependencies; // keep an array of dependencies in order to remove them
  private $Subprojects; // keep an array of supbprojects in order to remove them
  
  /** Constructor */
  public function __construct($projectid)
    {
    parent::__construct($projectid);
    }
  
  /** startElement function */
  public function startElement($parser, $name, $attributes)
    {
    parent::startElement($parser, $name, $attributes);
    // Check that the project name is correct
    if($name=='PROJECT')
      {
      if(get_project_id($attributes['NAME']) != $this->projectid)
        {
        add_log("Wrong project name","ProjectHandler:startElement");
        exit();
        }
      $this->Subprojects = array();  
      }
    else if($name=='SUBPROJECT')
      {
      $this->SubProject = new SubProject();
      $this->SubProject->SetProjectId($this->projectid);
      $this->SubProject->Name = $attributes['NAME'];
      $this->SubProject->Save();
      $this->Dependencies = array();
      $this->Subprojects[] = $this->SubProject->Id;
      }
    else if($name=='DEPENDENCY') 
      {
      $dependentProject = new SubProject();
      $dependentProject->Name = $attributes['NAME'];
      $dependentProject->SetProjectId($this->projectid);
      $dependencyid = $dependentProject->GetIdFromName();
      $this->Dependencies[] = $dependencyid;
      $this->SubProject->AddDependency($dependencyid);
      }
    } // end startElement
  
  /** endElement function */
  public function endElement($parser, $name)
    {
    parent::endElement($parser, $name);
    if($name=='SUBPROJECT')
      {  
      // Remove dependencies
      $dependencyids = $this->SubProject->GetDependencies();
      $removeids = array_diff($dependencyids,$this->Dependencies);
      foreach($removeids as $removeid)
        {
        $this->SubProject->RemoveDependency($removeid);
        }
      }
    else if($name=='PROJECT')
      {  
      // Remove subprojects
      $Project = new Project();
      $Project->Id = $this->projectid;
      $subprojectids = $Project->GetSubprojects();
      $removeids = array_diff($subprojectids,$this->Subprojects);
      foreach($removeids as $removeid)
        {
        $SubProject = new SubProject();
        $SubProject->ProjectId = $this->projectid;
        $SubProject->Id = $removeid;
        $SubProject->Delete();
        }
      
      }  
   } // end endElement

  /** text function */
  public function text($parser, $data)
    {
    //$parent = $this->getParent();
    //$element = $this->getElement();
    } // end function text
} // end class
?>