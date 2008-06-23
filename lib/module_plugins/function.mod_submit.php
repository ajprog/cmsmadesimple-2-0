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

function smarty_cms_function_mod_submit($params, &$smarty)
{
	$module =& $smarty->get_template_vars('cms_mapi_module');
	$id = $smarty->get_template_vars('cms_mapi_id');
	$translate = coalesce_key($params, 'translate', true, FILTER_VALIDATE_BOOLEAN);

	$value = ($translate === true) ? $module->lang($params['value']) : $params['value'];
	$confirm_text = ($translate === true) ? $module->Lang($params['confirm_text']) : $params['confirm_text'];

	$addtext = '';
	$class = coalesce_key($params,'class','');
	if( !empty($class) )
		{
			$addtext = 'class="'.$class.'"';
		}
	$tmp = coalesce_key($params,'additional_text','');
	if( !empty($tmp) )
		{
			if( empty($addtext) )
				{
					$addtext=$tmp;
				}
			else
				{
					$addtext .= ' '.$tmp;
				}
		}
	return $module->create_input_submit($id, $params['name'], $value, 
										$addtext, 
										coalesce_key($params, 'image', ''), 
										$confirm_text, coalesce_key($params, 'id', ''));
}

# vim:ts=4 sw=4 noet
?>