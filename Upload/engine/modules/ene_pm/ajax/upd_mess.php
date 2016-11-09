<?PHP
/*
=====================================================
Ene PM - Модуль быстрых сообщений для CMS DLE
-----------------------------------------------------
Автор : Gameer
-----------------------------------------------------
Site : http://gameer.name/
-----------------------------------------------------
Copyright (c) 2016 Gameer
=====================================================
Данный код защищен авторскими правами и использует лицензию CC Attribution — Noncommercial — Share Alike
*/
@error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );
@ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

define( 'DATALIFEENGINE', true );
define( 'ROOT_DIR', substr( dirname(  __FILE__ ), 0, -27 ) );
define( 'ENGINE_DIR', ROOT_DIR . '/engine' );

include ENGINE_DIR . '/data/config.php';
require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';
require_once ENGINE_DIR . '/classes/templates.class.php';

dle_session();
require_once ENGINE_DIR . '/modules/sitelogin.php';

require_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';
@header( "Content-type: text/html; charset=" . $config['charset'] );

define( 'TEMPLATE_DIR', ROOT_DIR . '/templates/' . $config['skin'] );

if(!$is_logged) $member_id['user_group'] = 5;

$user_from_id = isset($_POST["user_id"]) && is_numeric($_POST["user_id"]) ? intval($_POST["user_id"]) : false;
$ajax_upd = isset($_POST["ajax_upd"]) && is_numeric($_POST["ajax_upd"]) ? intval($_POST["ajax_upd"]) : 15;
$msfg = isset($_POST["msfg"]) && is_string($_POST["msfg"]) ? trim(strip_tags(stripslashes($_POST["msfg"]))) : "fff";
if(!$user_from_id) return;

$tpl_message = new dle_template();
$tpl_message->dir = TEMPLATE_DIR;
$tpl_message->load_template( 'ene_pm/chat_message.tpl' );
$count_mess = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_ene_pm WHERE (user_id='{$user_from_id}' AND from_user_id='{$member_id[user_id]}') OR ( user_id='{$member_id[user_id]}' AND from_user_id='{$user_from_id}')");
$allmessagenow = $count_mess["count"]; 
if($count_mess["count"] <= 15)
{
	$count_message = "0, 15";
}
else
{
	if($msfg == "fff")
	{
		$from_mess = $count_mess["count"] - 15;
		$count_message = "{$from_mess}, 15";
	}
	else
	{
		$from_mess = $count_mess["count"] - $ajax_upd;
		$count_message = "{$from_mess}, {$ajax_upd}";
	}
}
$db->free($count_mess);
$sql = $db->query("SELECT e.from_user_id, e.text, e.date, e.user_id as my_id, e.read_mess, u.name, u.fullname, u.foto, u.lastdate FROM " . PREFIX . "_ene_pm as e, " . PREFIX . "_users as u  WHERE ( e.user_id=u.user_id AND e.user_id='{$user_from_id}' AND e.from_user_id='{$member_id[user_id]}') OR ( e.from_user_id=u.user_id AND e.user_id='{$member_id[user_id]}' AND e.from_user_id='{$user_from_id}') ORDER BY `date` ASC LIMIT {$count_message}");
$flag_mess = false;
$num_rows = $sql->num_rows;
if($num_rows > 0)
{
	while ($row = $db->get_row($sql))
	{	
		$row["date"] = date("d-m-Y", strtotime($row["date"])); 
		if($date_last != $row["date"])
		{			
			$tpl_message->set_block( "'\\[date\\](.*?)\\[/date\\]'si", "\\1" );
			$tpl_message->set( '{date}', $row["date"]);
		}
		else
		{
			$tpl_message->set_block( "'\\[date\\](.*?)\\[/date\\]'si", "" );
		}
		$date_last = $row["date"];
		if($row["read_mess"] == 0 && $row["my_id"] == $member_id["user_id"]) $flag_mess = true;

		$last_date = $row["date"];
		if($member_id["user_id"] == $row["from_user_id"])
		{
			$tpl_message->set_block( "'\\[me\\](.*?)\\[/me\\]'si", "\\1" );
			$tpl_message->set_block( "'\\[not-me\\](.*?)\\[/not-me\\]'si", "" );
		}
		else
		{
			$tpl_message->set_block( "'\\[me\\](.*?)\\[/me\\]'si", "" );
			$tpl_message->set_block( "'\\[not-me\\](.*?)\\[/not-me\\]'si", "\\1" );
		}
		$tpl_message->set( '{user_id}', intval($row["from_user_id"]) );
		$tpl_message->set( '{my_id}', intval($row["my_id"]) );
		$tpl_message->set( '{nick}', $row["name"] );
		$text_msg = trim($row["text"]);
		$tpl_message->set( '{message}', stripslashes(htmlspecialchars_decode($text_msg)));
		$fullname_o = $row["fullname"];
		$name_o = $row["name"];
		if($row["fullname"])
		{
			$tpl_message->set( '{fullname}', $row["fullname"] );
			$tpl_message->set_block( "'\\[fullname\\](.*?)\\[/fullname\\]'si", "\\1" );
			$tpl_message->set_block( "'\\[not_fullname\\](.*?)\\[/not_fullname\\]'si", "" );
		}
		else
		{
			$tpl_message->set_block( "'\\[fullname\\](.*?)\\[/fullname\\]'si", "" );
			$tpl_message->set_block( "'\\[not_fullname\\](.*?)\\[/not_fullname\\]'si", "\\1" );	
		}
		if($row["read_mess"] == 0 && $row["from_user_id"] == $member_id["user_id"])
		{
			$tpl_message->set_block( "'\\[not-read\\](.*?)\\[/not-read\\]'si", "\\1" );
			$tpl_message->set_block( "'\\[read\\](.*?)\\[/read\\]'si", "" );
		}
		else
		{
			$tpl_message->set_block( "'\\[not-read\\](.*?)\\[/not-read\\]'si", "" );
			$tpl_message->set_block( "'\\[read\\](.*?)\\[/read\\]'si", "\\1" );
		}
		if ( ($row['lastdate'] + 1200) > $_TIME )
		{
			$onlineblock = true;
			$tpl_message->set_block( "'\\[online\\](.*?)\\[/online\\]'si", "\\1" );
			$tpl_message->set_block( "'\\[offline\\](.*?)\\[/offline\\]'si", "" );
		}
		else
		{
			$onlineblock = false;
			$tpl_message->set_block( "'\\[offline\\](.*?)\\[/offline\\]'si", "\\1" );
			$tpl_message->set_block( "'\\[online\\](.*?)\\[/online\\]'si", "" );
		}
		if($row["foto"]) 
		{
			if ( count(explode("@", $row["foto"])) == 2 )
				$tpl_message->set( '{foto}', '//www.gravatar.com/avatar/' . md5(trim($row["foto"])) . '?s=' . intval($user_group[$row['user_group']]['max_foto']) );
			else 
			{
				if($config['version_id'] >= '10.5') 
				{								
					if (strpos($row["foto"], "//") === 0) $avatar = "http:".$row["foto"]; else $avatar = $row["foto"];
					$avatar = @parse_url ( $avatar );
					if( $avatar['host'] )
						$tpl_message->set( '{foto}', $row["foto"] );
					else
						$tpl_message->set( '{foto}', $config['http_home_url'] . "uploads/fotos/" . $row["foto"] );
				} 
				else
					if( $row["foto"] and (file_exists( ROOT_DIR . "/uploads/fotos/" . $row["foto"] )) ) $tpl_message->set( '{foto}', $config['http_home_url'] . "uploads/fotos/" . $row["foto"] );
			}
		}
		else
			$tpl_message->set( '{foto}', "{THEME}/dleimages/noavatar.png" );
		
		$tpl_message->compile('chat_message');
	}
}
else
{
	$row = $db->super_query("SELECT name, lastdate FROM " . PREFIX . "_users WHERE user_id='{$user_from_id}'");
	if ( ($row['lastdate'] + 1200) > $_TIME )
	{
		$onlineblock = true;
	}
	else
	{
		$onlineblock = false;
	}
	$name_o = $row["name"];
}
$tpl_message->clear();
$tpl_message->result['chat_message'] = str_ireplace('{THEME}', $config["http_home_url"] . '/templates/' . $config['skin'], $tpl_message->result['chat_message']);
if($tpl_message->result['chat_message']) $data["message"] = $tpl_message->result['chat_message'];
if($flag_mess) $data["newmess"] = "ok";
if($onlineblock)
{
	$data["onlineblock"] = "ok";
}
$data["nick"] = $name_o;
$data["allmessagenow"] = $allmessagenow;
$data["id"] = $user_from_id;
$obj = json_encode($data);
unset($data);
echo $obj;