<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: testimage.php 2567 2010-07-27 15:07:22Z zach.mullen $
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

/** Test Image 
 *  Actually stores just the image id. The image is supposed to be already in the image table */
class TestImage
{
  var $Id;
  var $Role;
  var $TestId;  
  
  /** Return if exists */
  function Exists()
    {
    $query = pdo_query("SELECT count(*) AS c FROM test2image WHERE imgid='".$this->Id."' AND testid='".$this->TestId."' AND role='".$this->Role."'");  
    $query_array = pdo_fetch_array($query);
    if($query_array['c']>0)
      {
      return true;
      }
    return false;
    }      
      
  // Save in the database
  function Insert()
    {
    $role = pdo_real_escape_string($this->Role);

    $query = "INSERT INTO test2image (imgid,testid,role)
              VALUES ('$this->Id','$this->TestId','$role')";                     
    if(!pdo_query($query))
      {
      add_last_sql_error("TestImage Insert");
      return false;
      }
    return true;
    }  // end Insert  
}
?>
