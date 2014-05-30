<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: coveragesummarydiff.php 1396 2009-02-03 19:09:26Z jjomier $
  Language:  PHP
  Date:      $Date: 2009-02-03 19:09:26 +0000 (Tue, 03 Feb 2009) $
  Version:   $Revision: 1396 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
class CoverageSummaryDiff
{
  var $LocTested;
  var $LocUntested;
  var $BuildId;
  
  function Insert()
    {
    pdo_query("INSERT INTO coveragesummarydiff (buildid,loctested,locuntested) 
              VALUES(".qnum($this->BuildId).",".qnum($this->LocTested).",".qnum($this->LocUntested).")");
    add_last_sql_error("CoverageSummary:ComputeDifference");
    }
}
?>
