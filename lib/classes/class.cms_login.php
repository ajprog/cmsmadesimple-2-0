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
 * Class to hold static methods for various aspects of the admin panel's 
 * inner working and security.
 *
 * @author Ted Kulp
 * @since 2.0
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 * @license GPL
 **/
class CmsLogin extends CmsObject
{
	static private $instance = NULL;

	function __construct()
	{
		parent::__construct();
	}
	
	static public function get_instance()
	{
		if (self::$instance == NULL)
		{
			self::$instance = new CmsLogin();
		}
		return self::$instance;
	}
	
	/**
	 * Checks to see if the user is logged in.   If not, redirects the browser
	 * to the admin login.
	 *
	 * @since 0.1
	 * @param string no_redirect - If true, then don't redirect if not logged in
	 * @return boolean If they're logged in, true.  If not logged in, false. 
	 */
	static public function check_login($no_redirect = false)
	{
		$config = cms_config();

		//Handle a current login if one is in queue in the SESSION
		if (isset($_SESSION['login_user_id']))
		{
			//debug_buffer("Found login_user_id.  Going to generate the user object.");
			self::generate_user_object($_SESSION['login_user_id']);
			unset($_SESSION['login_user_id']);
		}

		if (isset($_SESSION['login_cms_language']))
		{
			//debug_buffer('Setting language to: ' . $_SESSION['login_cms_language']);
			setcookie('cms_language', $_SESSION['login_cms_language'], 0, self::get_cookie_path());
			unset($_SESSION['login_cms_language']);
		}

		if (!isset($_SESSION["cmsms_user_id"]))
		{
			//debug_buffer('No session found.  Now check for cookies');
			if (isset($_COOKIE["cmsms_user_id"]) && isset($_COOKIE["cmsms_passhash"]))
			{
				debug_buffer('Cookies found, do a passhash check');
				if (self::check_passhash(isset($_COOKIE["cmsms_user_id"]), isset($_COOKIE["cmsms_passhash"])))
				{
					//debug_buffer('passhash check succeeded...  creating session object');
					self::generate_user_object($_COOKIE["cmsms_user_id"]);
				}
				else
				{
					//debug_buffer('passhash check failed...  redirect to login');
					$_SESSION["redirect_url"] = $_SERVER["REQUEST_URI"];
					if (false == $no_redirect)
					{
						redirect($config["root_url"]."/".$config['admin_dir']."/login.php");
					}
					var_dump('redirect 1');
					return false;
				}
			}
			else
			{
				//debug_buffer('No cookies found.  Redirect to login.');
				$_SESSION["redirect_url"] = $_SERVER["REQUEST_URI"];
				if (false == $no_redirect)
				{
					redirect($config["root_url"]."/".$config['admin_dir']."/login.php");
				}
				var_dump('redirect 2');
				return false;
			}
		}
		else
		{
			//debug_buffer('Session found.  Moving on...');
			return true;
		}
	}
	
	static public function handle_login_request($redirect_url, &$username, &$error, $check_for_upgrade = false)
	{
		if (isset($_POST['username'])) $username = CmsRequest::clean_value($_POST['username']);

		if (isset($_POST['username']) && isset($_POST['password']))
		{
			$password = '';
			if (isset($_POST['password'])) $password = $_POST['password'];

			if ($username != '' && $password != '' && isset($_POST['loginsubmit']))
			{
				if (CmsLogin::login($username, $password))
				{
					if ($check_for_upgrade)
					{
						// redirect to upgrade if db_schema it's old
						$current_version = $CMS_SCHEMA_VERSION;

						$query = 'SELECT version from '.cms_db_prefix().'version';
						$row = cms_db()->GetRow($query);
						if ($row) $current_version = $row['version'];

						if ($current_version < $CMS_SCHEMA_VERSION)
						{
							redirect(CmsConfig::get('root_url') . '/install/upgrade.php');
						}
						// end of version check
					}

					if (isset($_SESSION['redirect_url']))
					{
						$tmp = $_SESSION['redirect_url'];
						unset($_SESSION['redirect_url']);
						redirect($tmp);
					}
					else
					{
						redirect($redirect_url, true);
					}
				}
				else
				{
					$error .= lang('usernameincorrect');
				}
			}
			else
			{
				$error .= lang('usernameincorrect');
			}
		}
	}
	
	/**
	 * Given the username and password, will login the user, generate the proper session 
	 * and cookie credentials.  It will return true if the login was successful, or false if
	 * it wasn't.  Reasons could include an invalid username or a wrong password.
	 *
	 * @param string The usernname to login as.
	 * @param string The password to check.  This should be unhashed.
	 * @return bool Whether or not the login was successful
	 * @author Ted Kulp
	 */
	static public function login($username, $password)
	{
		$oneuser = cms_orm('CmsUser')->find_by_username($username);

		if ($oneuser != null && $oneuser->password == md5($password))
		{
			self::generate_user_object($oneuser->id);
			$_SESSION['login_cms_language'] = get_preference($oneuser->id, 'default_cms_language');
			CmsEventManager::send_event('Core:LoginPost', array('user' => &$oneuser));
			//audit($oneuser->id, $oneuser->username, 'User Login');
			
			return true;
		}
		
		//TODO: Is sending the wrong password a bad idea?
		CmsEventManager::send_event('Core:LoginFailed', array('username' => $username, 'password' => $password));
		
		return false;
	}
	
	/**
	 * Given the id, will login the user, generate the proper session 
	 * and cookie credentials.  It will return true if the login was successful, or false if
	 * it wasn't.  Reasons could include an invalid username or a wrong password.
	 *
	 * @param string The usernname to login as.
	 * @param string The password to check.  This should be unhashed.
	 * @return bool Whether or not the login was successful
	 * @author Ted Kulp
	 */
	static public function login_by_id($user_id)
	{
		$oneuser = cms_orm('CmsUser')->find_by_id($user_id);

		if ($oneuser != null)
		{
			self::generate_user_object($oneuser->id);
			CmsEventManager::send_event('Core:LoginPost', array('user' => &$oneuser));
			//audit($oneuser->id, $oneuser->username, 'User Login');
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns the currently logged in user.  If noone is logged into the system, then a 
	 * CmsAnonymousUser object is returned.  An easy check of $user->is_anonymous() will determine 
	 * if someone is logged in or not.
	 *
	 * @return CmsUser The user (or anonymous user) logged into the system.
	 * @author Ted Kulp
	 */
	static public function get_current_user()
	{
		//1. Check session
		//Return user (only if we're not in debug -- it's hard to debug users if it's being put in the session)
		//if (!in_debug() && array_key_exists('cmsms_user', $_SESSION))
		if (array_key_exists('cmsms_user', $_SESSION))
		{
			return $_SESSION['cmsms_user'];
		}

		//2. Check cookie
		//Generate session, return user
		if (array_key_exists('cmsms_user_id', $_COOKIE) && array_key_exists('cmsms_passhash', $_COOKIE))
		{
			if (self::check_passhash($_COOKIE['cmsms_user_id'], $_COOKIE['cmsms_passhash']))
			{
				return self::generate_user_object($_COOKIE['cmsms_user_id']);
			}
		}

		//3. Return anonymous user
		return self::get_anonymous_user();
	}
	
	/**
	 * Old method of getting the userid of the currently logged in user.

	 *
	 * @return CmsUser If they're logged in, the user id.  If not logged in, null.
	 * @since 0.1
	 */
	static public function get_user($check = true)
	{
		if ($check)
		{
			self::check_login(); //It'll redirect out to login if it fails
		}

		if (isset($_SESSION["cmsms_user"]))
		{
			return $_SESSION["cmsms_user"];
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * Gets the userid of the currently logged in user.
	 *
	 * @return integer If they're logged in, the user id.  If not logged in, null.
	 * @since 0.1
	 */
	static public function get_userid($check = true)
	{
		$user = self::get_user($check);
		
		if ($user != null)
		{
			return $user->id;
		}
		
		return null;
	}
	
	static public function check_passhash($userid, $checksum)
	{
		$oneuser = cms_orm('CmsUser')->find_by_id($userid);
		if ($oneuser && $checksum == md5(md5(ROOT_DIR . '--' . $oneuser->password)))
		{
			return true;
		}

		return false;
	}
	
	static public function generate_user_object($userid)
	{
		$oneuser = cms_orm('CmsUser')->find_by_id($userid);
		if ($oneuser)
		{
			$_SESSION['cmsms_user'] = $oneuser;
			$_SESSION['cmsms_user_id'] = $oneuser->id; //TODO: Remove me
			if (!headers_sent())
			{
				setcookie('cmsms_user_id', $oneuser->id, 0, self::get_cookie_path());
				setcookie('cmsms_passhash', md5(md5(ROOT_DIR . '--' . $oneuser->password)), 0, self::get_cookie_path());
			}
			return $oneuser;
		}
		
		return self::get_anonymous_user();
	}
	
	static public function get_anonymous_user()
	{
		return new CmsAnonymousUser();
	}
	
	static public function logout()
	{
		unset($_SESSION['cmsms_user']);
		unset($_SESSION['cmsms_user_id']); //TODO: Remove me
		if (!headers_sent())
		{
			setcookie('cmsms_user_id', '', time() - 3600, self::get_cookie_path());
			setcookie('cmsms_passhash', '', time() - 3600, self::get_cookie_path());
		}
	}
	
	static public function get_cookie_path()
	{
		$result = '';
		
		$parts = parse_url(CmsConfig::get('root_url'));
		if (isset($parts['path']))
		{
			$result = $parts['path'];
			if (!ends_with($result, '/'))
			{
				$result .= '/';
			}
		}
		
		return $result;
	}
}

# vim:ts=4 sw=4 noet
?>
