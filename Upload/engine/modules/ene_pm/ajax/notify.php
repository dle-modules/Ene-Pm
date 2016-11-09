<?PHP
/*
=====================================================
Ene PM - Модуль быстрых сообщений для CMS DLE
-----------------------------------------------------
Автор оригинала: MSW (https://0-web.ru/dle/free-mod-dle/420-pm-notifier-v22.html)
Автор : Gameer
-----------------------------------------------------
Site : http://gameer.name/
-----------------------------------------------------
Copyright (c) 2016 Gameer
=====================================================
Данный код защищен авторскими правами и использует лицензию CC Attribution — Noncommercial — Share Alike
*/
if($_POST["check"] == "update")
{
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

if(!$is_logged) $member_id['user_group'] = 5;
$idblock = isset($_POST["idblock"]) && is_string($_POST["idblock"]) ? trim(strip_tags(stripslashes($_POST["idblock"]))) : false;
if($idblock && $idblock != 0)
{
	$inchat = " AND from_user_id NOT IN ($idblock) ";
	$incheck = " AND from_user_id IN ($idblock) ";
}
else { $inchat = ""; $incheck = "";}
$sql = $db->query("SELECT e.from_user_id, e.text, u.foto, u.name FROM " . PREFIX . "_ene_pm as e, " . PREFIX . "_users as u  WHERE e.from_user_id=u.user_id AND e.user_id='{$member_id[user_id]}' AND e.readpoup='0' {$inchat} GROUP BY from_user_id ORDER BY `date` ASC LIMIT 4");
$db->query("UPDATE " . PREFIX . "_ene_pm SET `readpoup`='1' WHERE `user_id`='{$member_id[user_id]}' {$incheck}");
$num_rows = $sql->num_rows;
if($num_rows > 0)
{

	while ($row = $db->get_row($sql))
	{
		if($row["foto"]) 
		{
			if ( count(explode("@", $row["foto"])) == 2 )
				$foto = '//www.gravatar.com/avatar/' . md5(trim($row["foto"])) . '?s=' . intval($user_group[$row['user_group']]['max_foto']);
			else 
			{
				if($config['version_id'] >= '10.5') 
				{								
					if (strpos($row["foto"], "//") === 0) $avatar = "http:".$row["foto"]; else $avatar = $row["foto"];
					$avatar = @parse_url ( $avatar );
					if( $avatar['host'] )
						$foto = $row["foto"];
					else
						$foto = $config['http_home_url'] . "uploads/fotos/" . $row["foto"];
				} 
				else
					if( $row["foto"] and (file_exists( ROOT_DIR . "/uploads/fotos/" . $row["foto"] )) ) $foto = $config['http_home_url'] . "uploads/fotos/" . $row["foto"];
			}
		}
		else
			$foto = "{THEME}/dleimages/noavatar.png";
		$text = str_replace("<br />", " ", $row['text']);
		$text = htmlspecialchars_decode(trim(strip_tags(htmlspecialchars_decode($text), "<img><a>")));
		$text = dle_substr(strip_tags(stripslashes($text)),0,200, $config['charset'])." ...";
		$js .= "$(\"#pm_notifier_new\").notify({ speed:1000, expires:15000 }).notify(\"create\", { text:'<img onclick=\"ShowChatUser({$row[from_user_id]});return false;\" src=\"{$foto}\" style=\"float: left; height: 50px; margin-right: 10px;\" /><h1 onclick=\"ShowChatUser({$row[from_user_id]});return false;\">Новое сообщение от: {$row[name]}</h1><div onclick=\"ShowChatUser({$row[from_user_id]});return false;\" style=\"color:#fff;\">{$text}<br clear=\"all\"></div>' });\n";
	}
$js = str_ireplace('{THEME}', $config["http_home_url"] . '/templates/' . $config['skin'], $js);
$db->free($sql);
echo <<<HTML
<script language="javascript" type="text/javascript">
<!--
{$js}
//-->
</script>
<div id="pm_notifier_new">
	<div id="themeroller">
		<a class="ui-notify-close" href="#"><span class="ui-notify_close" style="float:right"></span></a>
		<p>#{text}</p>
	</div>
</div>
<audio src="/uploads/new_msg.mp3" autoplay></audio>
HTML;
}
}
else
{
if(!defined('DATALIFEENGINE')) die("Hacking attempt!");
echo <<<HTML
<script>
function check_messagepoup() {
	var idblocks = [];
	if($("[data-chatblock]").length)
	{
		$( "[data-chatblock]" ).each(function( index ) {
			idblocks.push($( this ).attr("data-chatblock"));
		});
	}
	else
	{
		idblocks.push(0);
	}
	var idallblock = idblocks.join(",");
	$.post(dle_root + "engine/modules/ene_pm/ajax/notify.php", {check:"update", idblock : idallblock},
		function(data){
			$('#pm_notifier').html(data);
		}
	);
}
check_messagepoup();
setInterval("check_messagepoup()", 5000);
</script>
<div id="pm_notifier" style="position: absolute;cursor: pointer;"></div>
HTML;
}