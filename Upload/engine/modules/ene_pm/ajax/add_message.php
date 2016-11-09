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

include_once ENGINE_DIR . '/classes/parse.class.php';
$parse = new ParseFilter();
$parse->safe_mode = true;
$parse->allow_url = 1;
$parse->allow_image = 1;

$user_from_id = isset($_POST["user_id"]) && is_numeric($_POST["user_id"]) ? intval($_POST["user_id"]) : false;
$text_msg = isset($_POST["text_msg"]) ? $_POST["text_msg"] : false;
$date_last = isset($_POST["date_last"]) && is_string($_POST["date_last"]) ? trim(strip_tags(stripslashes($_POST["date_last"]))) : false;
if(!$user_from_id || !$text_msg || !$date_last) return;

$tpl_message = new dle_template();
$tpl_message->dir = TEMPLATE_DIR;
$tpl_message->load_template( 'ene_pm/chat_message.tpl' );

$text_msg = convert_unicode( $text_msg, $config['charset'] );

$parse->allowbbcodes = false;
$text_msg = $db->safesql( $parse->BB_Parse( $parse->process( trim( $text_msg ) ), false ) );

if(date("Y.m.d", time()) != date("Y.m.d", $date_last))
{
	$tpl_message->set_block( "'\\[date\\](.*?)\\[/date\\]'si", "\\1" );
	$tpl_message->set( '{date}', date("d-m-Y", time()) );
}
else
{
	$tpl_message->set_block( "'\\[date\\](.*?)\\[/date\\]'si", "" );
}
$tpl_message->set_block( "'\\[not-read\\](.*?)\\[/not-read\\]'si", "\\1" );
$tpl_message->set_block( "'\\[read\\](.*?)\\[/read\\]'si", "" );
if($user_from_id != $member_id["user_id"])
{
	$tpl_message->set_block( "'\\[me\\](.*?)\\[/me\\]'si", "\\1" );
	$tpl_message->set_block( "'\\[not-me\\](.*?)\\[/not-me\\]'si", "" );
}
else
{
	$tpl_message->set_block( "'\\[me\\](.*?)\\[/me\\]'si", "" );
	$tpl_message->set_block( "'\\[not-me\\](.*?)\\[/not-me\\]'si", "\\1" );
}
$tpl_message->set( '{user_id}', intval($user_from_id) );
$tpl_message->set( '{my_id}', intval($member_id["my_id"]) );
$tpl_message->set( '{nick}', $member_id["name"] );
$tpl_message->set( '{message}', stripslashes(htmlspecialchars_decode($text_msg)) );
if($member_id["fullname"])
{
	$tpl_message->set( '{fullname}', $member_id["fullname"] );
	$tpl_message->set_block( "'\\[fullname\\](.*?)\\[/fullname\\]'si", "\\1" );
	$tpl_message->set_block( "'\\[not_fullname\\](.*?)\\[/not_fullname\\]'si", "" );
}
else
{
	$tpl_message->set_block( "'\\[fullname\\](.*?)\\[/fullname\\]'si", "" );
	$tpl_message->set_block( "'\\[not_fullname\\](.*?)\\[/not_fullname\\]'si", "\\1" );	
}
if ( ($member_id['lastdate'] + 1200) > $_TIME )
{
	$tpl_message->set_block( "'\\[online\\](.*?)\\[/online\\]'si", "\\1" );
	$tpl_message->set_block( "'\\[offline\\](.*?)\\[/offline\\]'si", "" );
}
else
{
	$tpl_message->set_block( "'\\[offline\\](.*?)\\[/offline\\]'si", "\\1" );
	$tpl_message->set_block( "'\\[online\\](.*?)\\[/online\\]'si", "" );
}
if($member_id["foto"]) 
{
	if ( count(explode("@", $member_id["foto"])) == 2 )
		$tpl_message->set( '{foto}', '//www.gravatar.com/avatar/' . md5(trim($member_id["foto"])) . '?s=' . intval($user_group[$member_id['user_group']]['max_foto']) );
	else 
	{
		if($config['version_id'] >= '10.5') 
		{								
			if (strpos($member_id["foto"], "//") === 0) $avatar = "http:".$member_id["foto"]; else $avatar = $member_id["foto"];
			$avatar = @parse_url ( $avatar );
			if( $avatar['host'] )
				$tpl_message->set( '{foto}', $member_id["foto"] );
			else
				$tpl_message->set( '{foto}', $config['http_home_url'] . "uploads/fotos/" . $member_id["foto"] );
		} 
		else
			if( $member_id["foto"] and (file_exists( ROOT_DIR . "/uploads/fotos/" . $member_id["foto"] )) ) $tpl_message->set( '{foto}', $config['http_home_url'] . "uploads/fotos/" . $member_id["foto"] );
	}
}
else
	$tpl_message->set( '{foto}', "{THEME}/dleimages/noavatar.png" );


$date_mess = date("Y-m-d H:i:s", time());
$db->query("INSERT INTO " . PREFIX . "_ene_pm (`user_id`, `from_user_id`, `text`, `date`, `read_mess`, `readpoup`) VALUES ('{$user_from_id}', '{$member_id[user_id]}', '{$text_msg}', '{$date_mess}', '0','0')");
$db->free();
$tpl_message->compile('chat_message');
$tpl_message->clear();
$tpl_message->result["chat_message"] = str_ireplace('{THEME}', $config["http_home_url"] . '/templates/' . $config['skin'], $tpl_message->result["chat_message"]);
$data["message"] = $tpl_message->result['chat_message'];
$data["time"] = time();
unset($tpl_message->result['chat_message']);
$obj = json_encode($data);
unset($data);
echo $obj;