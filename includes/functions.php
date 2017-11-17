<?php
/**
 *
 * @author      Junaid
 * @category    Admin
 * @package     gsmarena-crawler/includes
 * @version     1.0
 */
/**
 * Refine Text
 * characters in [] are allowed
 *
 * @param string $str string to refine
 * @return string refined string
 */
function refineTEXT($str)
{
	return  preg_replace('/[^\w!@#$%^&*()+=:?.,><\/ -]+/', '', $str);
}

function DBin($string) 
{
	return  trim(htmlspecialchars($string,ENT_QUOTES));
}

function DBout($string) 
{
	$string = trim($string);
	return htmlspecialchars_decode($string,ENT_QUOTES);
}

/**
 * For a case-insensitive in_array(),
 * you can use array_map() to avoid a foreach statement
 *
 * @param unknown $needle
 * @param unknown $haystack
 * @return boolean
 */
function in_arrayi($needle, $haystack) {
	return in_array(strtolower($needle), array_map('strtolower', $haystack));
}


/**
 * extract spasific characters,
 * from string
 * 
 * @param string $str
 * @param string $from
 * @param string $to
 * @return string
 */
function extractor($str,$from,$to)
{
	$from_pos = strpos($str,$from);
	$from_pos = $from_pos + strlen($from);
	$to_pos   = strpos($str,$to,$from_pos);// to must be after from
	$return	  = substr($str,$from_pos,$to_pos-$from_pos);
	unset($str,$from,$to,$from_pos,$to_pos );
	return $return;
}

/**
 * 
 * look in provided path for 
 * files and folders and return
 * names of files or folders
 * 
 * @param string $path
 * @return array
 */
function load_resources($path) 
{
	$dir_handle = @opendir($path) or die("Appllication's directory structure problem.");
	$content_list = array ();
		while (($file = readdir($dir_handle)) != FALSE) 
		{
			if($file!="." && $file!="..") // not system folders
			  {
				$content_list []= $file;
			  }
		} // loop
			closedir($dir_handle);
			natcasesort($content_list);
			return $content_list;
}

/**
 * 
 * Enter job activity in controller
 * 
 * @param object $db
 * @param string $jobName
 * @param intiger $authUid
 * @param string $activity
 * @param boolean $update
 *    
 * @return void
 */
function jobController($db, $jobName, $authUid, $activity = "", $update = false)
{
	if ( $update == false)
	{
		$time 	 	  = time() + rand(1, 1000);
		$jobName      = $jobName . "_" . $time;
		
		$activity = (!empty($activity))?$activity:"starting...";
		
		$table 		  = "`controller`";
		$field_values = "`jobName` = '$jobName', "
					  . "`assigned_OAuth_user` = $authUid, "
					  . "`activity` = '$activity', "
					  . "`runningFrom` = ".time().", "
					  . "`lastReported` = ".time().", "
			  	  	  . "`status` = 1";
		
		$db->insert($table, $field_values);
	} else 
	{
		$table 		  = "`controller`";
		$field_values = "`assigned_OAuth_user` = $authUid, "
					  . "`activity` = '$activity', ";
		
		$where 		  = "`jobName` = '$jobName'";
		$db->update($table, $field_values, $where);
	}	
	
}



/**
 * string CustomStrStr($str,$needle,$position = false,$sub = false)
 * $str = "This is sample text. it is really simple example";
 * $position = false and $sub = false show result of before first occurance of $needle
 * $position = true and $sub false show result of before last occurance of $needle
 * $position = false and $sub = true show result of after first occurance of $needle
 * $position = true and $sub true show result of after last occurance of $needle
 */


function CustomStrStr($str,$needle,$position = false,$sub = false)
{
	$Isneedle = strpos($str,$needle);
	if ($Isneedle === false)
		return false;

	$needlePos =0;
	$return;
	if ( $position === false )
		$needlePos = strpos($str,$needle);
	else
		$needlePos = strrpos($str,$needle);

	if ($sub === false)
		$return = substr($str,0,$needlePos);
	else
		$return = substr($str,$needlePos+strlen($needle));

	return $return;
}


// if rate limit reach then switch new user
function remaining_calls_checker() {
		
	global $remainingCalls;
	global $remainingCallsCounter;
	global $authType;
	global $db;
	global $requestType;
	global $lb;
	global $tweetByUser;
		
	if ($remainingCallsCounter >= $remainingCalls) {

		if ( $authType == 'User' ) {

			$authID = $tweetByUser->twitterAuth->currentAthId;
			$app_only_auth = new app_only_auth($db);

			$if_auth = $app_only_auth->setAppAuth($requestType, $authID);
			if ($if_auth) {

				$tweetByUser->twitterAuth->tmhOAuth = $app_only_auth->tmhOAuth;
				$remainingCalls = $app_only_auth->remainingCalls; // remaining calls
				$remainingCallsCounter = 0;
				$authType = $app_only_auth->authType;

				echo "remainingCalls: " . $remainingCalls . $lb;
				echo "authType: " . $authType . $lb;
				echo "currentAthId: " . $app_only_auth->currentAthId . $lb;
				return true;
			} else {

				$tweetByUser->setAppAuth ();
				$remainingCalls = $tweetByUser->twitterAuth->remainingCalls;
				$remainingCallsCounter = 0;
				$authType = $tweetByUser->twitterAuth->authType;

				echo "remainingCalls: " . $remainingCalls . $lb;
				echo "authType: " . $authType . $lb;
				echo "currentAthId: " . $tweetByUser->twitterAuth->currentAthId . $lb;
				return true;
			}

		} else if ( $authType == 'AppOnly' ) {

			$tweetByUser->setAppAuth ();
			$remainingCalls = $tweetByUser->twitterAuth->remainingCalls;
			$remainingCallsCounter = 0;
			$authType = $tweetByUser->twitterAuth->authType;
				
			echo "remainingCalls: " .  $remainingCalls . $lb;
			echo "authType: " .  $authType . $lb;
			echo "currentAthId: " .  $tweetByUser->twitterAuth->currentAthId . $lb;
			return true;
		}
	}
	return false;
}


function refine_str($str,$from,$to="")
		{
		while (($from_pos = strpos($str,$from)) !== false )
		{
			if($to != "")
			{
				$to_pos   = strpos($str,$to,$from_pos);// to must be after from
				$str1 	  = substr($str,0,$from_pos);
				$str2 	  = substr($str,$to_pos+strlen($to));
				$str	  = $str1.$str2;
			}
			else
			{
				$str1 	  = substr($str,0,$from_pos);
				$str2 	  = substr($str,$from_pos+strlen($from));
				$str	  = $str1.$str2;
			}
		}
		unset ($str1,$str2);	
	return $str;
	}