<?php // -*- mode:php; tab-width:4; indent-tabs-mode:t; c-basic-offset:4; -*-
#CMS - CMS Made Simple
#(c)2004-2008 by Ted Kulp (ted@cmsmadesimple.org)
#This project's homepage is: http://cmsmadesimple.org
#
#This program is free software; you can redistribute it and/or modify
#it under the terms of the GNU General Public License as published by
#the Free Software Foundation; either version 2 of the License, or
#(at your option) any later version.
#
#This program is distributed in the hope that it will be useful,
#but WITHOUT ANY WARRANTY; without even the implied warranty of
#MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#GNU General Public License for more details.
#You should have received a copy of the GNU General Public License
#along with this program; if not, write to the Free Software
#Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
#$Id$

/**
 * Class for operations dealing with bulk content methods.
 *
 * @author Robert Campbell
 * @since 1.7
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 * @license GPL
 **/

class bulkcontentoperations {
  
  static public function register_function($label,$name,$module='core')
    {
      if( empty($name) || empty($label) ) return FALSE;

      $gCms = cmsms();
      if( !isset($gCms->variables['bulkcontent']) )
	{
	  $gCms->variables['bulkcontent'] = array();
	}
      $bulk =& $gCms->variables['bulkcontent'];

      $name = $module.'::'.$name;
      $bulk[$name] = $label;
      return TRUE;
      
    }


  static public function get_operation_list($separate_modules = true)
    {
      $gCms = cmsms();
      if( !isset($gCms->variables['bulkcontent']) )
	{
	  return FALSE;
	}

      $tmpc = array();
      $tmpm = array();
      foreach( $gCms->variables['bulkcontent'] as $name => $label )
	{
	  if( startswith($name,'core::') )
	    {
	      $tmpc[$name] = $label;
	    }
	  else
	    {
	      $tmpm[$name] = $label;
	    }
	}
      
      if( $separate_modules && count($tmpm) )
	{
	  $tmpc[-1] = '----------';
	}
      $tmpc = array_merge($tmpc,$tmpm);
      return $tmpc;
    }
} // end of class

?>