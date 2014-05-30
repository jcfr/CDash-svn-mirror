<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: buildgroupposition.php 2567 2010-07-27 15:07:22Z zach.mullen $
  Language:  PHP
  Date:      $Date: 2010-07-27 15:07:22 +0000 (Tue, 27 Jul 2010) $
  Version:   $Revision: 2567 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
// It is assumed that appropriate headers should be included before including this file
class BuildGroupPosition
{
  var $Position;
  var $StartTime;
  var $EndTime;
  var $GroupId;
  
  function __construct()
    {
    $this->StartTime = '1980-01-01 00:00:00';
    $this->EndTime = '1980-01-01 00:00:00';
    $this->Position = 1;
    }

  /** Check if the position already exists */  
  function Exists()
    {
    // If no id specify return false
    if(!$this->GroupId)
      {
      return false;    
      }
    
    $query = pdo_query("SELECT count(*) AS c FROM buildgroupposition WHERE 
                        buildgroupid='".$this->GroupId."' AND position='".$this->Position."'
                        AND starttime='".$this->StartTime."'
                        AND endtime='".$this->EndTime."'"
                        );
    $query_array = pdo_fetch_array($query);
    if($query_array['c']==0)
      {
      return false;
      }
    return true;  
    }  
    
  /** Save the goup position */
  function Add()
    {
    if(!$this->Exists())
      {
      if(!pdo_query("INSERT INTO buildgroupposition (buildgroupid,position,starttime,endtime)
                     VALUES ('$this->GroupId','$this->Position','$this->StartTime','$this->EndTime')"))
        {
        add_last_sql_error("BuildGroupPosition Insert()");
        return false;
        }
      return true;
      }  
    return false;
    } // end function save
}

?>
