<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: buildconfigureerrordiff.php 2567 2010-07-27 15:07:22Z zach.mullen $
  Language:  PHP
  Date:      $Date: 2010-07-27 15:07:22 +0000 (Tue, 27 Jul 2010) $
  Version:   $Revision: 2567 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
/** BuildConfigureErrorDiff class */
class BuildConfigureErrorDiff
{
  var $Type;
  var $Difference;
  var $BuildId;

    /** Return if exists */
  function Exists()
    {
    $query = pdo_query("SELECT count(*) AS c FROM configureerrordiff WHERE buildid=".qnum($this->BuildId));  
    $query_array = pdo_fetch_array($query);
    if($query_array['c']>0)
      {
      return true;
      }
    return false;
    }      
      
  /** Save in the database */
  function Save()
    {
    if(!$this->BuildId)
      {
      echo "BuildConfigureErrorDiff::Save(): BuildId not set";
      return false;    
      }
      
    if($this->Exists())
      {
      // Update
      $query = "UPDATE configureerrordiff SET";
      $query .= " type=".qnum($this->Type);
      $query .= ",difference=".qnum($this->Difference);
      $query .= " WHERE buildid=".qnum($this->BuildId);
      if(!pdo_query($query))
        {
        add_last_sql_error("BuildConfigureErrorDiff:Update",0,$this->BuildId);
        return false;
        }
      }
    else // insert  
      {
      $query = "INSERT INTO configureerrordiff (buildid,type,difference)
                 VALUES (".qnum($this->BuildId).",".qnum($this->Type).",".qnum($this->Difference).")";                     
      if(!pdo_query($query))
        {
        add_last_sql_error("BuildConfigureErrorDiff:Create",0,$this->BuildId);
        return false;
        }  
      }
    return true;
    }        
}
?>
