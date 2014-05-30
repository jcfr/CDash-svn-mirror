<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: constants.php 2336 2010-05-04 21:19:12Z jjomier $
  Language:  PHP
  Date:      $Date: 2010-05-04 21:19:12 +0000 (Tue, 04 May 2010) $
  Version:   $Revision: 2336 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
/** WARNING: JUST ADD TO THIS TABLE. NEVER MODIFY THE ENUMS */
define("CDASH_JOB_SCHEDULED","0");
define("CDASH_JOB_RUNNING","2");
define("CDASH_JOB_FINISHED","3");
define("CDASH_JOB_ABORTED","4");
define("CDASH_JOB_FAILED","5");

define("CDASH_JOB_EXPERIMENTAL","0");
define("CDASH_JOB_NIGHTLY","1");
define("CDASH_JOB_CONTINUOUS","2");

define("CDASH_REPOSITORY_CVS","0");
define("CDASH_REPOSITORY_SVN","1");

define("CDASH_OBJECT_PROJECT","1");
define("CDASH_OBJECT_BUILD","2");
define("CDASH_OBJECT_UPDATE","3");
define("CDASH_OBJECT_CONFIGURE","4");
define("CDASH_OBJECT_TEST","5");
define("CDASH_OBJECT_COVERAGE","6");
define("CDASH_OBJECT_DYNAMICANALYSIS","7");
define("CDASH_OBJECT_USER","8");
?>
