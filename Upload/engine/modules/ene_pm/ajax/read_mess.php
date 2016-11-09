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

dle_session();
require_once ENGINE_DIR . '/modules/sitelogin.php';

require_once ROOT_DIR . '/language/' . $config['langs'] . '/website.lng';
@header( "Content-type: text/html; charset=" . $config['charset'] );

define( 'TEMPLATE_DIR', ROOT_DIR . '/templates/' . $config['skin'] );

if(!$is_logged) $member_id['user_group'] = 5;
$user_from_id = isset($_POST["user_id"]) && is_numeric($_POST["user_id"]) ? intval($_POST["user_id"]) : false;
if(!$user_from_id) return;
if($db->query("UPDATE " . PREFIX . "_ene_pm SET `read_mess`='1' WHERE `user_id`='{$member_id[user_id]}' AND `from_user_id`='{$user_from_id}'"))
{
	$data["suc"] = "read by {$member_id[user_id]} from {$user_from_id} successful";
}
else
{
	$data["suc"] = "read by {$member_id[user_id]} from {$user_from_id} failed";
}
$obj = json_encode($data);
unset($data);
echo $obj;