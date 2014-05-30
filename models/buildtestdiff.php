<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: buildtestdiff.php 2608 2010-08-03 15:31:17Z jjomier $
  Language:  PHP
  Date:      $Date: 2010-08-03 15:31:17 +0000 (Tue, 03 Aug 2010) $
  Version:   $Revision: 2608 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
/** Build Test Diff */
class BuildTestDiff
{
  var $Type;
  var $DifferenceNegative;
  var $DifferencePositive;
  var $BuildId;

  // Insert in the database
  function Insert()
    {
    if(!$this->BuildId)
      {
      echo "BuildTestDiff::Insert(): BuildId is not set<br>";
      return false;
      }

    if($this->Type != 0 && empty($this->Type))
      {
      echo "BuildTestDiff::Insert(): Type is not set<br>";
      return false;
      }
      
    if(!is_numeric($this->DifferenceNegative))
      {
      echo "BuildTestDiff::Insert(): DifferenceNegative is not set<br>";
      return false;
      } 
       
    if(!is_numeric($this->DifferencePositive))
      {
      echo "BuildTestDiff::Insert(): DifferencePositive is not set<br>";
      return false;
      }
      
    $query = "INSERT INTO testdiff (buildid,type,difference_negative,difference_positive) 
              VALUES ('$this->BuildId','$this->Type','$this->DifferenceNegative','$this->DifferencePositive')";                     
    if(!pdo_query($query))
      {
      add_last_sql_error("BuildTestDiff Insert",0,$this->BuildId);
      return false;
      }  
    return true;
    }      
}

?>
