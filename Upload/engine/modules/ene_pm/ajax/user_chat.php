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

// [START] Стандартные include, функции, параметры DLE
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
// [END] Стандартные include, функции, параметры DLE

if(!$is_logged) return;
$user_from_id = isset($_GET["user_id"]) && is_numeric($_GET["user_id"]) ? intval($_GET["user_id"]) : false;
if(!$user_from_id) return;

if(file_exists(ENGINE_DIR . "/data/ene_pm.php"))
	include ENGINE_DIR . "/data/ene_pm.php";

$tpl = new dle_template();
$tpl->dir = TEMPLATE_DIR;
$tpl->load_template( 'ene_pm/chat_block.tpl' );

$count_mess = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_ene_pm WHERE (user_id='{$user_from_id}' AND from_user_id='{$member_id[user_id]}') OR ( user_id='{$member_id[user_id]}' AND from_user_id='{$user_from_id}')"); // подсчет сообщений
$tpl->set( '{allpm}', $count_mess["count"] );
if($count_mess["count"] <= 15) // вывод последние 15 сообщений
{
	$count_messages = "0, 15";
}
else
{
	$from_mess = $count_mess["count"] - 15;
	$count_messages = "{$from_mess}, 15";
}
$db->free($count_mess);

$sql = $db->query("SELECT e.from_user_id, e.text, e.date, e.user_id as my_id, e.read_mess, u.name, u.fullname, u.foto, u.lastdate FROM " . PREFIX . "_ene_pm as e, " . PREFIX . "_users as u  WHERE ( e.user_id=u.user_id AND e.user_id='{$user_from_id}' AND e.from_user_id='{$member_id[user_id]}') OR ( e.from_user_id=u.user_id AND e.user_id='{$member_id[user_id]}' AND e.from_user_id='{$user_from_id}') ORDER BY `date` ASC LIMIT {$count_messages}");
$num_rows = $sql->num_rows;
$tpl->set_block( "'\\[block\\](.*?)\\[/block\\]'si", "\\1" );
$tpl->set( '{id}', $user_from_id );
if($num_rows > 0)
{
	$tpl->set_block( "'\\[last_block\\](.*?)\\[/last_block\\]'si", "\\1" );
	$tpl_message = new dle_template();
	$tpl_message->dir = TEMPLATE_DIR;
	$tpl_message->load_template( 'ene_pm/chat_message.tpl' );
	$z_index = 0;
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
		$text_msg = stripslashes(htmlspecialchars_decode($row["text"]));
		$tpl_message->set( '{message}', $text_msg );
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
		if ( ($row['lastdate'] + 1200) > $_TIME )
		{
			$flag_onlined = true;
			$tpl_message->set_block( "'\\[online\\](.*?)\\[/online\\]'si", "\\1" );
			$tpl_message->set_block( "'\\[offline\\](.*?)\\[/offline\\]'si", "" );
		}
		else
		{
			$flag_onlined = false;
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
	$tpl_message->clear();
	
	$tpl->set_block( "'\\[last_message\\](.*?)\\[/last_message\\]'si", "\\1" );
	$tpl->set( '{message}',  $tpl_message->result['chat_message']);
	$tpl->set( '{nick}', $name_o );
	$tpl->set( '{last-time}', strtotime($last_date));
	if($flag_mess)
		$tpl->set( '{unread}', "0" );
	else
		$tpl->set( '{unread}', "1" );
	if($flag_onlined)
	{
		$tpl->set_block( "'\\[online\\](.*?)\\[/online\\]'si", "\\1" );
		$tpl->set_block( "'\\[offline\\](.*?)\\[/offline\\]'si", "" );
	}
	else
	{
		$tpl->set_block( "'\\[offline\\](.*?)\\[/offline\\]'si", "\\1" );
		$tpl->set_block( "'\\[online\\](.*?)\\[/online\\]'si", "" );
	}
	if($fullname_o)
	{
		$tpl->set( '{fullname}', $fullname_o );
		$tpl->set_block( "'\\[fullname\\](.*?)\\[/fullname\\]'si", "\\1" );
		$tpl->set_block( "'\\[not_fullname\\](.*?)\\[/not_fullname\\]'si", "" );
	}
	else
	{
		$tpl->set_block( "'\\[fullname\\](.*?)\\[/fullname\\]'si", "" );
		$tpl->set_block( "'\\[not_fullname\\](.*?)\\[/not_fullname\\]'si", "\\1" );	
	}
}
else // если сообщений не было, выводим просто данные о пользователе
{
	$row = $db->super_query("SELECT * FROM " . PREFIX . "_users WHERE user_id='{$user_from_id}'");
	$tpl->set( '{nick}', $row["name"] );
	$name_o = $row["name"];
	$tpl->set( '{last-time}', time());
	if ( ($member_id['lastdate'] + 1200) > $_TIME )
	{
		$tpl->set_block( "'\\[online\\](.*?)\\[/online\\]'si", "\\1" );
		$tpl->set_block( "'\\[offline\\](.*?)\\[/offline\\]'si", "" );
	}
	else
	{
		$tpl->set_block( "'\\[offline\\](.*?)\\[/offline\\]'si", "\\1" );
		$tpl->set_block( "'\\[online\\](.*?)\\[/online\\]'si", "" );
	}
	if($row["fullname"])
	{
		$tpl->set( '{fullname}', $row["fullname"] );
		$tpl->set_block( "'\\[fullname\\](.*?)\\[/fullname\\]'si", "\\1" );
		$tpl->set_block( "'\\[not_fullname\\](.*?)\\[/not_fullname\\]'si", "" );
	}
	else
	{
		$tpl->set_block( "'\\[fullname\\](.*?)\\[/fullname\\]'si", "" );
		$tpl->set_block( "'\\[not_fullname\\](.*?)\\[/not_fullname\\]'si", "\\1" );	
	}
	$tpl->set( '{unread}', "1" );
	$tpl->set_block( "'\\[last_message\\](.*?)\\[/last_message\\]'si", "" );
	$tpl->set_block( "'\\[not_last_message\\](.*?)\\[/not_last_message\\]'si", "\\1" );
	$tpl->set_block( "'\\[last_block\\](.*?)\\[/last_block\\]'si", "\\1" );
	
}
$array_smile = array();
$files = scandir(ROOT_DIR . "/templates/" . $config['skin'] . "/ene_pm/front_end/emoji/");
foreach($files as $file)
{
	$file_info = pathinfo(ROOT_DIR . "/templates/" . $config['skin'] . "/ene_pm/front_end/emoji/" . $file);
	if($file_info["extension"] == "png") $array_smile[] = "<a class=\"emoji_smile_cont emoji_smile_shadow\">
		<div class=\"emoji_bg\"></div>
		<div class=\"emoji_shadow\"></div>
		<img class=\"emoji\" onmouseover=\"ShowSmileP({$user_from_id});return false;\" onclick=\"PsSmile({$user_from_id},'{THEME}/ene_pm/front_end/emoji/{$file_info[filename]}.png');ShowSmileP({$user_from_id});return false;\" src=\"{THEME}/ene_pm/front_end/emoji/{$file_info[filename]}.png\">
	</a>";
}
if(count($array_smile) > 0) $tpl->set( '{emoji}', implode("", $array_smile) );
else $tpl->set( '{emoji}', "" );

$db->free();
	
$tpl->compile('chat_ene');
$tpl->clear();
$tpl->result["chat_ene"] = str_ireplace('{THEME}', $config["http_home_url"] . '/templates/' . $config['skin'], $tpl->result["chat_ene"]);
$data["block"] = $tpl->result['chat_ene'];
unset($tpl->result['chat_ene']);
$obj = json_encode($data);
unset($data);
echo $obj;