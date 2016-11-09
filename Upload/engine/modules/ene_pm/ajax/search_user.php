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

$q = isset($_POST["q"]) && is_string($_POST["q"]) ? trim(strip_tags(stripslashes($_POST["q"]))) : false;
if(!$q) return;
$sql = $db->query("SELECT user_id, lastdate, foto, name FROM " . PREFIX . "_users WHERE `name` LIKE '%{$q}%' AND `name`!='{$member_id[name]}'");
$num_rows = $sql->num_rows;
if($num_rows > 0)
{
	while ($row = $db->get_row($sql))
	{
		if ( ($row['lastdate'] + 1200) > $_TIME ) $online = "online";
		if($row["foto"]) 
		{
			if ( count(explode("@", $row["foto"])) == 2 ) $foto =  '//www.gravatar.com/avatar/' . md5(trim($row["foto"])) . '?s=' . intval($user_group[$row['user_group']]['max_foto']);
			else 
			{
				if($config['version_id'] >= '10.5') 
				{								
					if (strpos($row["foto"], "//") === 0) $avatar = "http:".$row["foto"]; else $avatar = $row["foto"];
					$avatar = @parse_url ( $avatar );
					if( $avatar['host'] ) $foto = $row["foto"];
					else $foto = $config['http_home_url'] . "uploads/fotos/" . $row["foto"];
				} 
				else
					if( $row["foto"] and (file_exists( ROOT_DIR . "/uploads/fotos/" . $row["foto"] )) ) $foto = $config['http_home_url'] . "uploads/fotos/" . $row["foto"];
			}
		}
		else $foto = "{THEME}/dleimages/noavatar.png";
		
		$buffer .= "<a class=\"fc_contact clear_fix fc_contact_over\" onclick=\"ShowChatUser({$row[user_id]});return false;\">
					<span class=\"fc_contact_photo {$online}\">
						<img src=\"{$foto}\" class=\"fc_contact_photo\">
					</span>
					<span class=\"fc_contact_status\"></span>
					<span class=\"fc_contact_name\">{$row[name]}</span>
				</a>";
	}
	$buffer = str_ireplace('{THEME}', $config["http_home_url"] . '/templates/' . $config['skin'], $buffer);
	echo $buffer;
}
else echo "Ничего не найдено.";