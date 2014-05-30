<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: buildconfigureerror.php 2567 2010-07-27 15:07:22Z zach.mullen $
  Language:  PHP
  Date:      $Date: 2010-07-27 15:07:22 +0000 (Tue, 27 Jul 2010) $
  Version:   $Revision: 2567 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/

/** BuildConfigureError class */
class BuildConfigureError
{
  var $Type;
  var $Text;
  var $BuildId;
  
  /** Return if exists */
  function Exists()
    {
    if(!$this->BuildId || !is_numeric($this->BuildId))
      {
      echo "BuildConfigureError::Save(): BuildId not set";
      return false;    
      }
      
    if(!$this->Type || !is_numeric($this->Type) )
      {
      echo "BuildConfigureError::Save(): Type not set";
      return false;    
      }
        
    $query = pdo_query("SELECT count(*) AS c FROM configureerror WHERE buildid='".$this->BuildId."'
                         AND type='".$this->Type."' AND text='".$this->Text."'");  
    add_last_sql_error("BuildConfigureError:Exists",0,$this->BuildId);
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
    if(!$this->BuildId || !is_numeric($this->BuildId))
      {
      echo "BuildConfigureError::Save(): BuildId not set";
      return false;    
      }
      
    if(!$this->Type || !is_numeric($this->Type))
      {
      echo "BuildConfigureError::Save(): Type not set";
      return false;    
      }
        
    if(!$this->Exists())
      {
      $text = pdo_real_escape_string($this->Text);
      $query = "INSERT INTO configureerror (buildid,type,text)
                VALUES (".qnum($this->BuildId).",".qnum($this->Type).",'$text')";                     
      if(!pdo_query($query))
        {
        add_last_sql_error("BuildConfigureError:Save",0,$this->BuildId);
        return false;
        }  
      }
    return true;
    }        
}
?>