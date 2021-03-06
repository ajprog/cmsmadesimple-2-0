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

require_once(cms_join_path(ROOT_DIR,'lib','smarty','Smarty.class.php'));

class CmsSmarty extends Smarty
{
	static private $instance = NULL;
	
	public $id = '';
	
	function __construct()
	{
		parent::__construct();
		global $CMS_ADMIN_PAGE;
		
		$config = cms_config();

		$this->plugins_dir = array(cms_join_path(ROOT_DIR, 'lib', 'smarty', 'plugins'), cms_join_path(ROOT_DIR, 'plugins'), cms_join_path(ROOT_DIR, 'lib', 'module_plugins'));

		if (isset($CMS_ADMIN_PAGE) && $CMS_ADMIN_PAGE == 1)
		{
			$this->template_dir = $config["root_path"].'/'.$config['admin_dir'].'/templates/';
			$this->config_dir = $config["root_path"].'/'.$config['admin_dir'].'/configs/';
			$this->plugins_dir[] = cms_join_path(ROOT_DIR, $config['admin_dir'], 'plugins');
		}
		else
		{
			$this->template_dir = ROOT_DIR.'/tmp/templates/';
			$this->config_dir = ROOT_DIR.'/tmp/configs/';
		}
		
		$this->compile_dir = TMP_TEMPLATES_C_LOCATION;
		$this->cache_dir = TMP_CACHE_LOCATION;
		
		$this->deprecation_notices = false;

		//use_sub_dirs doesn't work in safe mode
		//if (ini_get("safe_mode") != "1")
		//	$this->use_sub_dirs = true;
		//$this->caching = false;
		//$this->compile_check = true;
		$this->assign('app_name','CMS');
		$this->debugging = false;
		//$this->force_compile = false;
		$this->cache_plugins = false;

		if ($config["debug"] == true)
		{
			//$this->caching = false;
			$this->force_compile = true;
			//$this->debugging = true;
		}

		if (CmsApplication::is_sitedown())
		{
			$this->caching = false;
			$this->force_compile = true;
		}

		if (isset($CMS_ADMIN_PAGE) && $CMS_ADMIN_PAGE == 1)
		{
			$this->caching = false;
			$this->force_compile = true;
		}

		$this->register->resource("db", array(&$this, "cms_template_get_template",
						       "cms_template_get_timestamp",
						       "db_get_secure",
						       "db_get_trusted"));
		$this->register->resource("print", array(&$this, "cms_template_get_template",
						       "cms_template_get_timestamp",
						       "db_get_secure",
						       "db_get_trusted"));
		$this->register->resource("template", array(&$this, "cms_template_get_template",
						       "cms_template_get_timestamp",
						       "db_get_secure",
						       "db_get_trusted"));
		$this->register->resource("tpl_top", array(&$this, "template_top_get_template",
						       "cms_template_get_timestamp",
						       "db_get_secure",
						       "db_get_trusted"));
		$this->register->resource("tpl_head", array(&$this, "template_head_get_template",
						       "cms_template_get_timestamp",
						       "db_get_secure",
						       "db_get_trusted"));
		$this->register->resource("tpl_body", array(&$this, "template_body_get_template",
						       "cms_template_get_timestamp",
						       "db_get_secure",
						       "db_get_trusted"));
		$this->register->resource("htmlblob", array(&$this, "global_content_get_template",
						       "global_content_get_timestamp",
						       "db_get_secure",
						       "db_get_trusted"));
		$this->register->resource("globalcontent", array(&$this, "global_content_get_template",
						       "global_content_get_timestamp",
						       "db_get_secure",
						       "db_get_trusted"));
		$this->register->resource("content", array(&$this, "content_get_template",
						       "content_get_timestamp",
						       "db_get_secure",
						       "db_get_trusted"));
		$this->register->resource("module", array(&$this, "module_get_template",
						       "module_get_timestamp",
						       "db_get_secure",
						       "db_get_trusted"));
		$this->register->resource("module_db_tpl", array(&$this, "module_db_template",
						       "module_db_timestamp",
						       "db_get_secure",
						       "db_get_trusted"));
		$this->register->resource("module_file_tpl", array(&$this, "module_file_template",
						       "module_file_timestamp",
						       "db_get_secure",
						       "db_get_trusted"));
	}
	
	/**
	 * Returns an instnace of the CmsSmarty singleton.  Most 
	 * people can generally use cms_smarty() instead of this, but they 
	 * both do the same thing.
	 *
	 * @return CmsSmarty The singleton CmsSmarty instance
	 * @author Ted Kulp
	 **/
	static public function get_instance()
	{
		if (!isset(self::$instance))
		{
			if (!defined('SMARTY_DIR'))
			{
				define('SMARTY_DIR', cms_join_path(ROOT_DIR, 'lib', 'smarty') . DIRECTORY_SEPARATOR);
			}
			
			self::$instance = new CmsSmarty();
			
			//CmsSmarty::load_plugins();
		}
		return self::$instance;
	}

    /**
     * wrapper for include() retaining $this
     * @return mixed
     */
    function _include($filename, $once=false, $params=null)
    {
        if ($filename != '')
        {
			if ($once) {
				return include_once($filename);
			} else {
				return include($filename);
			}
        }
    }

    function trigger_error($error_msg, $error_type = E_USER_WARNING)
    {   
        var_dump("Smarty error: $error_msg");
    }

	function module_file_template($tpl_name, &$tpl_source, $smarty_obj)
    {
        //5.3 $params = explode(';', $tpl_name);
		$params = explode(';', $tpl_name);
		$config = cms_config();
		$root_path = $config['root_path'];

		if( count($params) == 2 )
		{
			$fns = array("{$root_path}/module_custom/{$params[0]}/templates/{$params[1]}",
						 "{$root_path}/modules/{$params[0]}/templates/{$params[1]}");
			for( $i = 0; $i < count($fns); $i++ )
			{
				if( file_exists($fns[$i]) )
				{
					$tpl_source = @file_get_contents($fns[$i]);
					return true;
				}
			}
		}
        return false;
    }

	function module_file_timestamp($tpl_name, &$tpl_timestamp, $smarty_obj)
	{
		//5.3 $params = explode(';', $tpl_name);
		$params = explode(';', $tpl_name);
		$config = cms_config();
		$root_path = $config['root_path'];

		if( count($params) == 2 )
		{
			$fns = array("{$root_path}/module_custom/{$params[0]}/templates/{$params[1]}",
						 "{$root_path}/modules/{$params[0]}/templates/{$params[1]}");
			for( $i = 0; $i < count($fns); $i++ )
			{
				if( file_exists($fns[$i]) )
				{
					$tpl_timestamp = filemtime($fns[$i]);
					return true;
				}
			}
		}
        return false;
	}

	function module_db_template($tpl_name, &$tpl_source, $smarty_obj)
	{
		$gCms = cmsms();
		$db = cms_db();
		$config = cms_config();
		
		$params = explode(';', $tpl_name);

		$row = null;
		
		if (count($params) == 2)
		{
			$query = "SELECT content from {module_templates} WHERE module_name = ? AND template_type = ? AND default_template = 1";
			$row = $db->GetRow($query, $params);
		}
		else if (count($params) == 3)
		{
			$query = "SELECT content from {module_templates} WHERE module_name = ? AND template_type = ? AND template_name = ?";
			$row = $db->GetRow($query, $params);
		}

		if ($row)
		{
			$tpl_source = $row['content'];
			return true;
		}

		return false;
	}

	function module_db_timestamp($tpl_name, &$tpl_timestamp, $smarty_obj)
	{
		$gCms = cmsms();
		$db = cms_db();
		$config = cms_config();
		
		$params = explode(';', $tpl_name);

		$row = null;
		
		//TODO: Make me cache, please?
		
		if (count($params) == 2)
		{
			$query = "SELECT modified_date from {module_templates} WHERE module_name = ? AND template_type = ? AND default_template = 1";
			$row = $db->GetRow($query, $params);
		}
		else if (count($params) == 3)
		{
			$query = "SELECT modified_date from {module_templates} WHERE module_name = ? AND template_type = ? AND template_name = ?";
			$row = $db->GetRow($query, $params);
		}

		if ($row)
		{
			$tpl_timestamp = $db->UnixTimeStamp($row['modified_date']);
			return true;
		}
		
		return false;
	}

	function global_content_get_template($tpl_name, &$tpl_source, $smarty_obj)
	{
		debug_buffer('start global_content_get_template');
		global $gCms;
		$config =& $gCms->config;
		$gcbops =& $gCms->GetGlobalContentOperations();

		$oneblob = $gcbops->LoadHtmlBlobByName($tpl_name);
		if ($oneblob)
		{
			$text = $oneblob->content;

			#Perform the content htmlblob callback
			/*
			reset($gCms->modules);
			while (list($key) = each($gCms->modules))
			{
				$value =& $gCms->modules[$key];
				if ($gCms->modules[$key]['installed'] == true &&
					$gCms->modules[$key]['active'] == true)
				{
					$gCms->modules[$key]['object']->ContentHtmlBlob($text);
				}
			}
			*/

			$tpl_source = $text;

			#So no one can do anything nasty, take out the php smarty tags.  Use a user
			#defined plugin instead.
			if (!(isset($config["use_smarty_php_tags"]) && $config["use_smarty_php_tags"] == true))
			{
				$tpl_source = preg_replace("/\{\/?php\}/", "", $tpl_source);
			}
		}
		else
		{
			$tpl_source = "<!-- Html blob '" . $tpl_name . "' does not exist  -->";
		}
		debug_buffer('end global_content_get_template');
		return true;
	}

	function global_content_get_timestamp($tpl_name, &$tpl_timestamp, $smarty_obj)
	{
		debug_buffer('start global_content_get_timestamp');
		global $gCms;
		$gcbops = $gCms->GetGlobalContentOperations();
		$oneblob = $gcbops->LoadHtmlBlobByName($tpl_name);
		if ($oneblob)
		{
			$tpl_timestamp = $oneblob->modified_date;
			debug_buffer('end global_content_get_timestamp');
			return true;
		}
		else
		{
			return false;
		}
	}

	function template_top_get_template($tpl_name, &$tpl_source, $smarty_obj)
	{
	  global $gCms;
	  $config =& $gCms->GetConfig();
	  
	  if (CmsApplication::is_sitedown())
	    {
	      $tpl_source = '';
	      return true;
	    }
	  else
	    {
	      if ($tpl_name == 'notemplate')
		{
		  $tpl_source = '';
		  return true;
		}

	      if( isset($gCms->variables['template']) )
		{
		  $tpl_source = $gCms->variables['template'];
		}
	      else
		{
		  $pageinfo = $gCms->variables['pageinfo'];
		  $templateops =& $gCms->GetTemplateOperations();
		  $templateobj =& $templateops->LoadTemplateByID($pageinfo->template_id);
		  if (isset($templateobj) && $templateobj !== FALSE)
		    {
		      $tpl_source = $templateobj->content;
		      $gCms->variables['template'] = $tpl_source;
		    }
		}
		 
	      $pos = stripos($tpl_source,'<head');
	      if( $pos === FALSE )
		{
		  // return the whole template
		  return true;
		}
	      $tpl_source = substr($tpl_source,0,$pos);
	      return true;
	    }
	  return false;
	}

	function template_head_get_template($tpl_name, &$tpl_source, $smarty_obj)
	{
	  global $gCms;
	  $config =& $gCms->GetConfig();
	  
	  if (CmsApplication::is_sitedown())
	    {
	      $tpl_source = '';
	      return true;
	    }
	  else
	    {
	      if ($tpl_name == 'notemplate')
		{
		  $tpl_source = '';
		  return true;
		}

	      if( isset($gCms->variables['template']) )
		{
		  $tpl_source = $gCms->variables['template'];
		}
	      else
		{
		  $pageinfo = $gCms->variables['pageinfo'];
		  $templateops =& $gCms->GetTemplateOperations();
		  $templateobj =& $templateops->LoadTemplateByID($pageinfo->template_id);
		  if (isset($templateobj) && $templateobj !== FALSE)
		    {
		      $tpl_source = $templateobj->content;
		      $gCms->variables['template'] = $tpl_source;
		    }
		}
		 
	      $pos1 = stripos($tpl_source,'<head');
	      $pos2 = stripos($tpl_source,'</head>');
	      if( $pos1 === FALSE || $pos2 === FALSE )
		{
		  // return an empty string
		  // assume it was processed in the top
		  $tpl_source = '';
		  return true;
		}
	      $tpl_source = substr($tpl_source,$pos1,$pos2-$pos1+7);
	      return true;
	    }
	  return false;
	}


	function template_body_get_template($tpl_name, &$tpl_source, $smarty_obj)
	{
	  global $gCms;
	  $config =& $gCms->GetConfig();
	  
	  if (CmsApplication::is_sitedown())
	    {
	      header('HTTP/1.0 503 Service Unavailable');
	      header('Status: 503 Service Unavailable');

	      $tpl_source = get_site_preference('sitedownmessage');
	      return true;
	    }
	  else
	    {
	      if ($tpl_name == 'notemplate')
		{
		  $tpl_source = '{content}';
		  return true;
		}

	      if( isset($gCms->variables['template']) )
		{
		  $tpl_source = $gCms->variables['template'];
		}
	      else
		{
		  $pageinfo = $gCms->variables['pageinfo'];
		  $templateops =& $gCms->GetTemplateOperations();
		  $templateobj =& $templateops->LoadTemplateByID($pageinfo->template_id);
		  if (isset($templateobj) && $templateobj !== FALSE)
		    {
		      $tpl_source = $templateobj->content;
		      $gCms->variables['template'] = $tpl_source;
		    }
		}
	      
	      $pos = stripos($tpl_source,'</head>');
	      if( $pos === FALSE )
		{
		  // this probably means it's not an html template
		  // just return an empty string
		  // and assume that the tpl_head stuff
		  // returned the entire template
		  $tpl_source = '';
		  return true;
		}

	      $tpl_source = substr($tpl_source,$pos+7);
	      return true;
	    }
	  return false;
	}

	function cms_template_get_template($tpl_name, &$tpl_source, $smarty_obj)
	{
		/*
		global $gCms;
		$config = cms_config();

		if (CmsApplication::is_sitedown())
		{
			$tpl_source = get_site_preference('sitedownmessage');
			return true;
		}
		else
		{
			$pageinfo = $gCms->variables['pageinfo'];

			if ($tpl_name == 'notemplate')
			{
				$tpl_source = '{content}';

				return true;
			}
			else if (isset($_GET["print"]))
			{
				// this should really just go.
				$script = '';

				if (isset($_GET["js"]) and $_GET["js"] == 1)
					$script = '<script type="text/javascript">window.print();</script>';

				if (isset($_GET["goback"]) and $_GET["goback"] == 0)
				{
					$tpl_source = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"'."\n".'"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">'.'<head><title>{title}</title><meta name="robots" content="noindex"></meta>{metadata}{stylesheet}{literal}<style type="text/css" media="print">#back {display: none;}</style>{/literal}</head><body style="background-color: white; color: black; background-image: none; text-align: left;">{content}'.$script.'</body></html>';
				}
				else
				{
					$hm =& $gCms->GetHierarchyManager();
					if ('mod_rewrite' == $config['url_rewriting'])
					{
						$curnode =& $hm->getNodeByAlias($tpl_name);
					}
					else
					{
						$curnode =& $hm->getNodeById($tpl_name);
					}
					$curcontent =& $curnode->GetContent();
					$page_url = $curcontent->GetURL();

					$tpl_source = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"'."\n".'"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">'.'<head><title>{title}</title><meta name="robots" content="noindex"></meta>{metadata}{stylesheet}{literal}<style type="text/css" media="print">#back {display: none;}</style>{/literal}</head><body style="background-color: white; color: black; background-image: none; text-align: left;"><p><a id="back" href="'.$page_url.'">&laquo; Go Back</a></p>{content}'.$script.'</body></html>';
				}

				return true;
			}
			if( isset($_SESSION['cms_preview']) )
			{
				return 'DEBUG: IN PREVIEW<br/>';

				// get serialized data filename
				$tpl_name = trim($_SESSION['cms_preview']);
				unset($_SESSION['cms_preview']);
				$fname = '';
				if (is_writable($config["previews_path"]))
				{
					$fname = cms_join_path($config["previews_path"] , $tpl_name);
				}
				else
				{
					$fname = cms_join_path(TMP_CACHE_LOCATION , $tpl_name);
				}
				if( !file_exists($fname) )
				{
					$tpl_source = 'Error: Cache file: '.$tpl_name.' does not exist.';
					return false;
				}

				// get the serialized data
				$handle = fopen($fname, "r");
				$data = unserialize(fread($handle, filesize($fname)));
				fclose($handle);
				unlink($fname);

				$tpl_source = $data["template"];

				return true;
			}
			else
			{
				global $gCms;
				$templateops = $gCms->GetTemplateOperations();
				$templateobj = $templateops->LoadTemplateByID($pageinfo->template_id);
				if (isset($templateobj) && $templateobj !== FALSE)
				{
					$tpl_source = $templateobj->content;

					#So no one can do anything nasty, take out the php smarty tags.  Use a user
					#defined plugin instead.
					if (!(isset($config["use_smarty_php_tags"]) && $config["use_smarty_php_tags"] == true))
					{
						$tpl_source = preg_replace("/\{\/?php\}/", "", $tpl_source);
					}

					//do_cross_reference($pageinfo->template_id, 'template', $tpl_source);

					return true;
				}
			}
			return false;
		}
		*/
		$template = cms_orm('CmsTemplate')->find_by_id($tpl_name);
		if ($template)
		{
			$tpl_source = $template->content;
			return true;
		}
		return false;
	}

	function cms_template_get_timestamp($tpl_name, &$tpl_timestamp, $smarty_obj)
	{
		/*
		global $gCms;

		if (CmsApplication::is_sitedown() || $tpl_name == 'notemplate')
		{
			$tpl_timestamp = time();
			return true;
		}
		else if (isset($_GET['id']) && isset($_GET[$_GET['id'].'showtemplate']) && $_GET[$_GET['id'].'showtemplate'] == 'false')
		{
			$tpl_timestamp = time();
			return true;
		}
		else if (isset($_GET['print']))
		{
			$tpl_timestamp = time();
			return true;
		}
		else
		{
			$pageinfo = &$gCms->variables['pageinfo'];

			$tpl_timestamp = $pageinfo->template_modified_date;
			return true;
		}
		*/
		$template = cms_orm('CmsTemplate')->find_by_id($tpl_name);
		if ($template)
		{
			$tpl_timestamp = $template->modified_date->timestamp();
			return true;
		}
		return false;
	}

	function content_get_template($tpl_name, &$tpl_source, $smarty_obj)
	{
		global $gCms;
		$config = cms_config();
		$pageinfo = &$gCms->variables['pageinfo'];

		if (isset($pageinfo) && $pageinfo->content_id == -1)
		{
			#We've a custom error message...  return it here
			header("HTTP/1.0 404 Not Found");
			header("Status: 404 Not Found");
			if ($tpl_name == 'content_en')
				$tpl_source = get_site_preference('custom404');
			else
				$tpl_source = '';
			return true;
		}
		else if( isset($_SESSION['cms_preview_data']) && $pageinfo->id == '__CMS_PREVIEW_PAGE__' )
		{
			if( !isset($_SESSION['cms_preview_data']['content_obj']) )
			{
				$_SESSION['cms_preview_data']['content_obj'] = CmsContentOperations::load_content_from_serialized_data($_SESSION['cms_preview_data']);
			}
			$contentobj =& $_SESSION['cms_preview_data']['content_obj'];
			$tpl_source = $contentobj->get_property_value($tpl_name);

			#So no one can do anything nasty, take out the php smarty tags.  Use a user
			#defined plugin instead.
			if (!(isset($config["use_smarty_php_tags"]) && $config["use_smarty_php_tags"] == true))
			{
				$tpl_source = ereg_replace("\{\/?php\}", "", $tpl_source);
			}

			return true;
		}
		else
		{
			/*
			$manager =& $gCms->GetHierarchyManager();
			$node =& $manager->sureGetNodeById($pageinfo->id);
			$contentobj =& $node->GetContent();
			*/
			
			$contentobj = CmsPageTree::get_instance()->get_node_by_id($pageinfo->id);

			if (isset($contentobj) && $contentobj !== FALSE)
			{
				$tpl_source = $contentobj->show($tpl_name);

				#So no one can do anything nasty, take out the php smarty tags.  Use a user
				#defined plugin instead.
				if (!(isset($config["use_smarty_php_tags"]) && $config["use_smarty_php_tags"] == true))
				{
					$tpl_source = preg_replace("/\{\/?php\}/", "", $tpl_source);
				}

				//do_cross_reference($pageinfo->id, 'content', $tpl_source);

				return true;
			}
		}
		return false;
	}

	function content_get_timestamp($tpl_name, &$tpl_timestamp, $smarty_obj)
	{
		$pageinfo = cmsms()->current_page;

		if (isset($pageinfo) && $pageinfo->id == -1)
		{
			#We've a custom error message...  set a current timestamp
			$tpl_timestamp = time();
		}
		else
		{
			if ($pageinfo->cachable)
			{
				$tpl_timestamp = $pageinfo->modified_date;
			}
			else
			{
				$tpl_timestamp = time();
			}
		}
		return true;
	}
	
	function module_get_template ($tpl_name, &$tpl_source, $smarty_obj)
	{
		$gCms = cmsms();
		$pageinfo = cmsms()->current_page;
		$config = cms_config();

		#Run the execute_user function and replace {content} with it's output 
		if (isset($gCms->modules[$tpl_name]))
		{
			@ob_start();

			$id = $smarty_obj->id;
			$returnid = isset($pageinfo)?$pageinfo->id:'';
			$params = GetModuleParameters($id);
			$action = 'default';
			if (isset($params['action']))
			{
				$action = $params['action'];
			}
			echo $gCms->modules[$tpl_name]['object']->DoActionBase($action, $id, $params, isset($pageinfo)?$pageinfo->content_id:'');
			$modoutput = @ob_get_contents();
			@ob_end_clean();

			$tpl_source = $modoutput;
		}
		
		header("Content-Type: ".$gCms->variables['content-type']."; charset=" . get_encoding());
		if (isset($gCms->variables['content-filename']) && $gCms->variables['content-filename'] != '')
		{
			header('Content-Disposition: attachment; filename="'.$gCms->variables['content-filename'].'"');
			header("Pragma: public");
		}

		#So no one can do anything nasty, take out the php smarty tags.  Use a user
		#defined plugin instead.
		if (!(isset($config["use_smarty_php_tags"]) && $config["use_smarty_php_tags"] == true))
		{
			$tpl_source = ereg_replace("\{\/?php\}", "", $tpl_source);
		}

		return true;
	}

	function module_get_timestamp($tpl_name, &$tpl_timestamp, $smarty_obj)
	{
		$tpl_timestamp = time();
		return true;
	}

	function db_get_secure($tpl_name, $smarty_obj)
	{
		// assume all templates are secure
		return true;
	}

	function db_get_trusted($tpl_name, $smarty_obj)
	{
		// not used for templates
	}
	
	/**
	 * Loads all plugins into the system
	 *
	 * @since 0.5
	 */
	public static function load_plugins()
	{
		$smarty = cms_smarty();
		
		$gCms = cmsms();
		$plugins = &$gCms->cmsplugins;
		$userplugins = &$gCms->userplugins;
		$userpluginfunctions = &$gCms->userpluginfunctions;
		$db = cms_db();
		
		if (isset($db) && $db->IsConnected())
		{
			#if (@is_dir(dirname(dirname(__FILE__))."/plugins/cache"))
			#{
			#	search_plugins($smarty, $plugins, dirname(dirname(__FILE__))."/plugins/cache", true);
			#}
			CmsSmarty::search_plugins($smarty, $plugins, cms_join_path(ROOT_DIR, 'plugins'), false);

			$query = "SELECT * FROM ".cms_db_prefix()."userplugins";
			$result = $db->Execute($query);
			while ($result && !$result->EOF)
			{
				if (!in_array($result->fields['userplugin_name'], $plugins))
				{
					$plugins[] =& $result->fields['userplugin_name'];
					$userplugins[$result->fields['userplugin_name']] = $result->fields['userplugin_id'];
					$functionname = "cms_tmp_".$result->fields['userplugin_name']."_userplugin_function";
					//Only register valid code
					if (!(@eval('function '.$functionname.'($params, &$smarty) {'.$result->fields['code'].'}') === FALSE))
					{
						$smarty->register->templateFunction($result->fields['userplugin_name'], $functionname, false);

						//Register the function in a hash so that we can call it from other places by name
						$userpluginfunctions[$result->fields['userplugin_name']] = $functionname;
					}
				}
				$result->MoveNext();
			}
			sort($plugins);
		}
		
	}
	
	public static function search_plugins(&$smarty, &$plugins, $dir, $caching)
	{
		global $CMS_LOAD_ALL_PLUGINS;

		$types = array('function', 'compiler', 'prefilter', 'postfilter', 'outputfilter', 'modifier', 'block');
		$handle = opendir($dir);
		while ($file = readdir($handle))
		{
			// This hides the dummy function.summarize.php
			// (function.summarize.php was renamed to modifier.summarize.php in 1.0.3)
			// This code can be deleted once the dummy is removed from the distribution
			// TODO: DELETE
			if (
				$file == 'function.summarize.php' &&
				substr(file_get_contents(cms_join_path($dir, $file)), 9, 9) == '__DUMMY__'
			)
			{
					continue;
			}
			// END TODO: DELETE

			$path_parts = pathinfo($file);
			if (isset($path_parts['extension']) && $path_parts['extension'] == 'php')
			{
				//Valid plugins will always have a 3 part filename
				$filearray = explode('.', $path_parts['basename']);
				if (count($filearray == 3))
				{
					$filename = cms_join_path($dir, $file);
					//The part we care about is the middle one...
					$file = $filearray[1];
					if (!isset($plugins[$file]) && in_array($filearray[0],$types))
					{
						$key = array_search($filearray[0], $types);
						switch ($key)
						{
							case 0:
									if (isset($CMS_LOAD_ALL_PLUGINS))
									{
										$plugins[] = $file;
										require_once($filename);
										$smarty->register->templateFunction($file, "smarty_cms_function_" . $file, $caching);
									}
									break;
							case 1:
									$plugins[] = $file;
									require_once($filename);
									$smarty->register->compilerFunction($file, "smarty_cms_compiler_" .  $file, $caching);
									break;
							case 2:
									$plugins[] = $file;
									require_once($filename);
									$smarty->register->preFilter("smarty_cms_prefilter_" . $file);
									break;
							case 3:
									$plugins[] = $file;
									require_once($filename);
									$smarty->register->postFilter("smarty_cms_postfilter_" . $file);
									break;
							case 4:
									$plugins[] = $file;
									require_once($filename);
									$smarty->register->outputFilter("smarty_cms_outputfilter_" . $file);
									break;
							case 5:
									$plugins[] = $file;
									require_once($filename);
									$smarty->register->modifier($file, "smarty_cms_modifier_" . $file);
									break;
							case 6:
									$plugins[] = $file;
									require_once($filename);
									$smarty->register->block($file, "smarty_cms_block_" . $file, $caching);
									break;
						}
					}
				}
			}
		}
		closedir($handle);
	}
	
    /**
    * wrapper function for Smarty 2 BC
    * 
    * @param string $tpl_var the template variable name
    * @param mixed $ &$value the referenced value to assign
    * @param boolean $nocache if true any output of this variable will be not cached
    * @param boolean $scope the scope the variable will have  (local,parent or root)
    */
	public function assign_by_ref($tpl_var, &$value, $nocache = false, $scope = SMARTY_LOCAL_SCOPE)
	{
		$this->assignByRef($tpl_var,$value,$nocache,$scope);
	}
}

# vim:ts=4 sw=4 noet
?>
