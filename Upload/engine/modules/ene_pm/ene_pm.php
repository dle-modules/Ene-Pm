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
	
	if(!function_exists('SetTemp'))
	{
		function SetTemp($tpl, &$row, $member_id, $db, $nnnn, $_TIME, $user_group, $config)
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
			$tpl->compile($nnnn);
			return $tpl;
		}
	}
	
	$tpl->load_template( 'ene_pm/ene_pm.tpl' );
	$sql_getlastuser = $db->query("SELECT e.from_user_id, e.user_id as uid, u.name, u.fullname, u.foto, u.lastdate FROM " . PREFIX . "_ene_pm as e, " . PREFIX . "_users as u  WHERE e.from_user_id=u.user_id AND e.user_id='{$member_id[user_id]}' GROUP BY `from_user_id` ORDER BY `date` DESC LIMIT 6");
	$num_rows = $sql_getlastuser->num_rows;
	$tpl->set_block( "'\\[block\\](.*?)\\[/block\\]'si", "\\1" );
	if($num_rows > 0)
	{
		$tpl->set_block( "'\\[last_block\\](.*?)\\[/last_block\\]'si", "\\1" );
		$tpl_message = new dle_template();
		$tpl_message->dir = TEMPLATE_DIR;
		$tpl_message->load_template( 'ene_pm/ene_pm_last.tpl' );
		$tpl_messages = new dle_template();
		$tpl_messages->dir = TEMPLATE_DIR;
		$tpl_messages->load_template( 'ene_pm/ene_pm_search.tpl' );
		while ($row = $db->get_row($sql_getlastuser))
		{
			$tpl_message = SetTemp($tpl_message, $row, $member_id, $db, "last_mess", $_TIME, $user_group, $config);
			$tpl_messages = SetTemp($tpl_messages, $row, $member_id, $db, "search_user", $_TIME, $user_group, $config);
		}
		$tpl_message->clear();
		$tpl_messages->clear();
		$tpl->set_block( "'\\[last_message\\](.*?)\\[/last_message\\]'si", "\\1" );
		$tpl->set( '{last_user}', $tpl_message->result['last_mess'] );
		$tpl->set( '{search}', $tpl_messages->result['search_user'] );
	}
	else
	{
		$db->free($sql_getlastuser);
		$sql_getlastuser = $db->query("SELECT e.from_user_id, e.user_id as uid, u.name, u.fullname, u.foto, u.lastdate, u.user_group FROM " . PREFIX . "_ene_pm as e, " . PREFIX . "_users as u  WHERE e.user_id=u.user_id AND e.from_user_id='{$member_id[user_id]}' GROUP BY `uid` ORDER BY `date` DESC LIMIT 6");
		$num_rows = $sql_getlastuser->num_rows;
		if($num_rows > 0)
		{
			$tpl->set_block( "'\\[last_block\\](.*?)\\[/last_block\\]'si", "\\1" );
			$tpl_message = new dle_template();
			$tpl_message->dir = TEMPLATE_DIR;
			$tpl_message->load_template( 'ene_pm/ene_pm_last.tpl' );
			$tpl_messages = new dle_template();
			$tpl_messages->dir = TEMPLATE_DIR;
			$tpl_messages->load_template( 'ene_pm/ene_pm_search.tpl' );
			while ($row = $db->get_row($sql_getlastuser))
			{
				$tpl_message = SetTemp($tpl_message, $row, $member_id, $db, "last_mess", $_TIME, $user_group, $config);
				$tpl_messages = SetTemp($tpl_messages, $row, $member_id, $db, "search_user", $_TIME, $user_group, $config);
			}
			$tpl_message->clear();
			$tpl_messages->clear();
			$tpl->set_block( "'\\[last_message\\](.*?)\\[/last_message\\]'si", "\\1" );
			$tpl->set( '{last_user}', $tpl_message->result['last_mess'] );
			$tpl->set( '{search}', $tpl_messages->result['search_user'] );
		}
		else
		{
			$tpl->set_block( "'\\[last_message\\](.*?)\\[/last_message\\]'si", "" );
			$tpl->set_block( "'\\[not_last_message\\](.*?)\\[/not_last_message\\]'si", "\\1" );
			$tpl->set_block( "'\\[last_block\\](.*?)\\[/last_block\\]'si", "\\1" );
		}
	}
	
	$db->free($sql_getlastuser);
	
	$date_online = $member_id['lastdate'] - 1200;
	$sql_online = $db->super_query("SELECT COUNT(*) as count FROM " . PREFIX . "_users WHERE `lastdate`>='{$date_online}' AND `user_id`!='{$member_id['user_id']}'");
	if($sql_online["count"]) $tpl->set( '{online}', intval($sql_online["count"]) );
	else $tpl->set( '{online}', 0 );
	
	$db->free();
	
	$tpl->compile('ene_pm');
	$tpl->clear();
	echo $tpl->result['ene_pm'];
?>