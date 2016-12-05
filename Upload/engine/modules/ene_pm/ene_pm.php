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
if( !defined( 'DATALIFEENGINE' ) ) die( "You are a fucking faggot!" );
if(file_exists(ENGINE_DIR . "/data/ene_pm.php"))
	include ENGINE_DIR . "/data/ene_pm.php";
if(!$ene_pm_cfg["status"] || $ene_pm_cfg["status"] != 1) return;

if(!function_exists('templateEnePM'))
{
	function templateEnePM($tpl, &$row, $member_id, $db, $name_template, $_TIME, $user_group, $config)
	{
		if($row["uid"] != $member_id["user_id"])
			$tpl->set( '{user_id}', intval($row["uid"]) );
		else
			$tpl->set( '{user_id}', intval($row["from_user_id"]) );
		$tpl->set( '{nick}', $row["name"] );
		
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
		if ( ($row['lastdate'] + 1200) > $_TIME )
		{
			$tpl->set_block( "'\\[online\\](.*?)\\[/online\\]'si", "\\1" );
			$tpl->set_block( "'\\[offline\\](.*?)\\[/offline\\]'si", "" );
		}
		else
		{
			$tpl->set_block( "'\\[offline\\](.*?)\\[/offline\\]'si", "\\1" );
			$tpl->set_block( "'\\[online\\](.*?)\\[/online\\]'si", "" );
		}
		if($row["uid"] != $member_id["user_id"])
			$message = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_ene_pm WHERE user_id='{$row[from_user_id]}' AND from_user_id='{$row[uid]}' AND read_mess='0'");
		else
			$message = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_ene_pm WHERE user_id='{$row[uid]}' AND from_user_id='{$row[from_user_id]}' AND read_mess='0'");
		if($message["count"] > 0)
		{
			$tpl->set( '{message}', $message["count"] );
			$tpl->set_block( "'\\[message\\](.*?)\\[/message\\]'si", "\\1" );
			$tpl->set_block( "'\\[not_message\\](.*?)\\[/not_message\\]'si", "" );
		}
		else
		{
			$tpl->set_block( "'\\[message\\](.*?)\\[/message\\]'si", "" );
			$tpl->set_block( "'\\[not_message\\](.*?)\\[/not_message\\]'si", "\\1" );
		}
		if($row["foto"]) 
		{
			if ( count(explode("@", $row["foto"])) == 2 )
				$tpl->set( '{foto}', '//www.gravatar.com/avatar/' . md5(trim($row["foto"])) . '?s=' . intval($user_group[$row['user_group']]['max_foto']) );
			else 
			{
				if($config['version_id'] >= '10.5') 
				{								
					if (strpos($row["foto"], "//") === 0) $avatar = "http:".$row["foto"]; else $avatar = $row["foto"];
					$avatar = @parse_url ( $avatar );
					if( $avatar['host'] )
						$tpl->set( '{foto}', $row["foto"] );
					else
						$tpl->set( '{foto}', $config['http_home_url'] . "uploads/fotos/" . $row["foto"] );
				} 
				else
					if( $row["foto"] and (file_exists( ROOT_DIR . "/uploads/fotos/" . $row["foto"] )) ) $tpl->set( '{foto}', $config['http_home_url'] . "uploads/fotos/" . $row["foto"] );
			}
		}
		else
			$tpl->set( '{foto}', "{THEME}/dleimages/noavatar.png" );
		$tpl->compile($name_template);
		return $tpl;
	}
}
if(intval($ene_pm_cfg["counts"]) > intval($ene_pm_cfg["blocks"]))
	$select = intval($ene_pm_cfg["counts"]) ?: 6;
elseif(intval($ene_pm_cfg["counts"]) < intval($ene_pm_cfg["blocks"]))
	$select = intval($ene_pm_cfg["blocks"]) ?: 6;
elseif(intval($ene_pm_cfg["counts"]) == intval($ene_pm_cfg["blocks"]))
	$select = intval($ene_pm_cfg["blocks"]) ?: 6;
$tpl->load_template( 'ene_pm/ene_pm.tpl' );
$sql_getlastuser = $db->query("SELECT e.from_user_id, e.user_id as uid, u.user_id as userid, u.name, u.fullname, u.foto, u.lastdate FROM " . PREFIX . "_ene_pm as e, " . PREFIX . "_users as u  WHERE (e.from_user_id=u.user_id AND e.user_id='{$member_id[user_id]}') OR (e.user_id=u.user_id AND e.from_user_id='{$member_id[user_id]}') GROUP BY userid ORDER BY `date` DESC LIMIT $select");
$num_rows = $sql_getlastuser->num_rows;
$tpl->set_block( "'\\[block\\](.*?)\\[/block\\]'si", "\\1" );
if($num_rows > 0)
{
	$tpl->set_block( "'\\[last_block\\](.*?)\\[/last_block\\]'si", "\\1" );
	$tpl_last_user = new dle_template();
	$tpl_last_user->dir = TEMPLATE_DIR;
	$tpl_last_user->load_template( 'ene_pm/ene_pm_last.tpl' );
	$tpl_search_user = new dle_template();
	$tpl_search_user->dir = TEMPLATE_DIR;
	$tpl_search_user->load_template( 'ene_pm/ene_pm_search.tpl' );
	$i_count_message = 0;
	while ($row = $db->get_row($sql_getlastuser))
	{
		if($i < intval($ene_pm_cfg["counts"]))
			$tpl_last_user = templateEnePM($tpl_last_user, $row, $member_id, $db, "last_mess", $_TIME, $user_group, $config);
		if($i < intval($ene_pm_cfg["blocks"]))
			$tpl_search_user = templateEnePM($tpl_search_user, $row, $member_id, $db, "search_user", $_TIME, $user_group, $config);
		$i++;
	}
	$tpl_last_user->clear();
	$tpl_search_user->clear();
	$tpl->set_block( "'\\[last_message\\](.*?)\\[/last_message\\]'si", "\\1" );
	$tpl->set( '{last_user}', $tpl_last_user->result['last_mess'] );
	$tpl->set( '{search}', $tpl_search_user->result['search_user'] );
}
else
{
	$tpl->set_block( "'\\[last_message\\](.*?)\\[/last_message\\]'si", "" );
	$tpl->set_block( "'\\[not_last_message\\](.*?)\\[/not_last_message\\]'si", "\\1" );
	$tpl->set_block( "'\\[last_block\\](.*?)\\[/last_block\\]'si", "\\1" );
}

$db->free($sql_getlastuser);
if($ene_pm_cfg["online"] == 1)
{
	$date_online = $member_id['lastdate'] - 1200; // подсчет пользователей в сети
	$sql_online = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_users WHERE `lastdate`>='{$date_online}' AND `user_id`!='{$member_id['user_id']}'");
	if($sql_online["count"]) $tpl->set( '{online}', intval($sql_online["count"]) );
	else $tpl->set( '{online}', 0 );
	$tpl->set_block( "'\\[online_on\\](.*?)\\[/online_on\\]'si", "\\1" );
	$tpl->set_block( "'\\[online_off\\](.*?)\\[/online_off\\]'si", "" );
}
else
{
	$tpl->set_block( "'\\[online_on\\](.*?)\\[/online_on\\]'si", "" );
	$tpl->set_block( "'\\[online_off\\](.*?)\\[/online_off\\]'si", "\\1" );
}
$db->free();

$tpl->compile('ene_pm');
$tpl->clear();

if($ene_pm_cfg["notife"] == 1)
{
$tpl->result['ene_pm'] .= <<<HTML
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
	$.post(dle_root + "engine/modules/ene_pm/ajax/notify.php", {idblock : idallblock},
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
echo $tpl->result['ene_pm'];
?>