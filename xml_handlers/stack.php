<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: stack.php 1306 2009-01-07 14:31:38Z jjomier $
  Language:  PHP
  Date:      $Date: 2009-01-07 14:31:38 +0000 (Wed, 07 Jan 2009) $
  Version:   $Revision: 1306 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even 
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR 
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
class Stack
{
  private $stack = array();
  
  public function __construct(){}
  
  public function size()
   { 
   return count($this->stack); 
   }

  public function push($e)
    {
    $this->stack[] = $e;
    return $this;
    }
  
  public function pop()
    {
    array_pop($this->stack);
    return $this;
    }
  
  public function top()
    {
    return $this->stack[count($this->stack)-1]; 
    }
  
  public function isEmpty()
    {
    return count($this->stack) == 0;
    }
  
  public function at($index)
    {
    if($index < 0 || $index >= count($this->stack))
      {
      return null;
      }
    return $this->stack[$index];
    }
}
?>
