<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: testmeasurement.php 2573 2010-07-27 16:31:10Z zach.mullen $
  Language:  PHP
  Date:      $Date: 2010-07-27 16:31:10 +0000 (Tue, 27 Jul 2010) $
  Version:   $Revision: 2573 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
// It is assumed that appropriate headers should be included before including this file

/** Test Measurement */
class TestMeasurement
{
  var $Name;
  var $Type;
  var $Value;
  var $TestId;
      
  // Save in the database
  function Insert()
    {
    $name = pdo_real_escape_string($this->Name);
    $type = pdo_real_escape_string($this->Type);
    $value = pdo_real_escape_string($this->Value);

    $query = "INSERT INTO testmeasurement (testid,name,type,value)
              VALUES ('$this->TestId','$name','$type','$value')";                     
    if(!pdo_query($query))
      {
      add_last_sql_error("TestMeasurement Insert");
      return false;
      }  
    return true;
    }  // end Insert  
    
}

?>
