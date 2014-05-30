<?php
/*=========================================================================

  Program:   CDash - Cross-Platform Dashboard System
  Module:    $Id: sax_handler.php 1306 2009-01-07 14:31:38Z jjomier $
  Language:  PHP
  Date:      $Date: 2009-01-07 14:31:38 +0000 (Wed, 07 Jan 2009) $
  Version:   $Revision: 1306 $

  Copyright (c) 2002 Kitware, Inc.  All rights reserved.
  See Copyright.txt or http://www.cmake.org/HTML/Copyright.html for details.

     This software is distributed WITHOUT ANY WARRANTY; without even 
     the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR 
     PURPOSE.  See the above copyright notices for more information.

=========================================================================*/
interface SaxHandler 
  {
  public function startElement($parser, $name, $attributes);
  public function endElement($parser, $name);
  public function text($parser, $data);
  public function processingInstruction($parser, $target, $data);
  public function externalEntity($parser, $open_entity_name, $base, $system_id, $public_id);
  public function skippedEntity($parser, $open_entity_name, $base, $system_id, $public_id);
  public function startPrefixMapping($parser, $user_data, $prefix, $uri);
  public function endPrefixMapping($parser, $user_data, $prefix);
  }
?>
