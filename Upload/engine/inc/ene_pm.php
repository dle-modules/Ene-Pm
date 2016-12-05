<?php
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
# Стандартная безопасность == START.
if (!defined('DATALIFEENGINE') OR !defined('LOGGED_IN')) {
    die("Hacking attempt!");
}

if ($member_id['user_group'] != 1) {
    msg("error", $lang['index_denied'], $lang['index_denied']);
}
# Стандартная безопасность == END.

# Функции для работы с панелью == START.
function showRow($title = "", $description = "", $field = "") {
	echo "<tr>
	<td class=\"col-xs-10 col-sm-6 col-md-7\"><h6>{$title}</h6><span class=\"note large\">{$description}</span></td>
	<td class=\"col-xs-2 col-md-5 settingstd\">{$field}</td>
	</tr>";
}
function showInput($name, $value)
{
	return "<input type='text' style=\"width: 400px;\" name=\"{$name}\" value=\"{$value}\">";
}
function showNumberIm($name, $value)
{
	if(trim($value) == "" || empty($value))
		$value = 15;
	return "<input type='number' style=\"width: 400px;\" name=\"{$name}\" min='9' value=\"{$value}\">";
}
function showNumberIs($name, $value)
{
	if(trim($value) == "" || empty($value))
		$value = 6;
	return "<input type='number' style=\"width: 400px;\" name=\"{$name}\" min='5' value=\"{$value}\">";
}
function makeCheckBox($name, $selected) {
	$selected = $selected ? "checked" : "";
	return "<input class=\"iButton-icons-tab\" type=\"checkbox\" name=\"$name\" value=\"1\" {$selected}>";
}
# Функции для работы с панелью == END.

# Подключаем конфиг == START.
if(file_exists(ENGINE_DIR . "/data/ene_pm.php"))
	include ENGINE_DIR . "/data/ene_pm.php";
include ROOT_DIR . "/language/EnePm/ene_pm_".$config["langs"].".lng";
# Подключаем конфиг == END.

if($action == "save")
{
	$save_con = $_POST['save_con'];
	$handler = fopen(ENGINE_DIR . '/data/ene_pm.php', "w");
	$save_con["counts"] = is_numeric($save_con["counts"]) && intval($save_con["counts"]) >= 5 ? intval($save_con["counts"]) : 6;
	$save_con["blocks"] = is_numeric($save_con["blocks"]) && intval($save_con["blocks"]) >= 5 ? intval($save_con["blocks"]) : 6;
	
	fwrite($handler, "<?PHP \n\n//Ene PM by Gameer\n\n\$ene_pm_cfg = [\n'version' => \"v2\",\n");
	foreach($save_con as $key => $value)
	{
		fwrite($handler, "\t\"{$key}\" => '$value',\n");
	}
	fwrite($handler, "];\n\n?>");
	fclose($handler);
	
	msg("info", $lang['opt_sysok'], "<b>{$lang['opt_sysok_1']}</b>", "$PHP_SELF?mod=ene_pm");
}
elseif($action == "messages")
{
if($_POST["do"] == "delete")
{
	if(isset($_POST["eid"]) && is_array($_POST["eid"]))
	{
		$id_array = [];
		foreach($_POST["eid"] as $key => $val)
		{
			if(is_numeric($val) && intval($val) > 0)
			{
				$db->query( "DELETE FROM " . PREFIX . "_ene_pm WHERE id='$val'" );
			}
		}
		msg( "info", $ene_pm_lang['delete_head'], $ene_pm_lang['delete_body'], "$PHP_SELF?mod=ene_pm");
	} else
		msg( "info", $ene_pm_lang['delete_head_error'], $ene_pm_lang['delete_body_error'], "$PHP_SELF?mod=ene_pm");
}
else
{
	echoheader("<i class=\"icon-envelope\"></i>" . $ene_pm_lang['title'] . " " . $ene_pm_lang["ver_txt"] . $ene_pm_lang["ver"], $ene_pm_lang['message_descr_menu']);

	if(isset($_GET["search_who"]) && $_GET["search_who"])
	{
		$search_who = $db->safesql(trim(strip_tags(stripslashes($_GET["search_who"]))));
		$user = $db->super_query("SELECT user_id FROM " . PREFIX . "_users WHERE name='{$search_who}'");
		if(intval($user["user_id"]) > 0)
		$where[] = "from_user_id={$user['user_id']}";
		$db->free($user);
	}

	if(isset($_GET["search_which"]) && $_GET["search_which"])
	{
		$search_which = $db->safesql(trim(strip_tags(stripslashes($_GET["search_which"]))));
		$user = $db->super_query("SELECT user_id FROM " . PREFIX . "_users WHERE name='{$search_which}'");
		if(intval($user["user_id"]) > 0)
		$where[] = "e.user_id={$user['user_id']}";
		$db->free($user);
	}
	
	if(isset($_GET["from_date"]) && $_GET["from_date"] && isset($_GET["to_date"]) && $_GET["to_date"])
	{
		$from_date = $db->safesql(trim(strip_tags(stripslashes($_GET["from_date"]))));
		$to_date = $db->safesql(trim(strip_tags(stripslashes($_GET["to_date"]))));
		$where[] = "e.date BETWEEN '{$from_date}' AND '{$to_date}'";
	}
	elseif(isset($_GET["from_date"]) && $_GET["from_date"] && !isset($_GET["to_date"]) && !$_GET["to_date"])
	{
		$from_date = $db->safesql(trim(strip_tags(stripslashes($_GET["from_date"]))));
		$where[] = "e.date >= '{$from_date}'";
	}
	elseif(!isset($_GET["from_date"]) && !$_GET["from_date"] && isset($_GET["to_date"]) && $_GET["to_date"])
	{
		$to_date = $db->safesql(trim(strip_tags(stripslashes($_GET["to_date"]))));
		$where[] = "e.date <= '{$to_date}'";
	}
	
	if(isset($_GET["status"]))
	{
		if(intval($_GET["status"]) != 2)
		{
			$status_mess = intval($_GET["status"]);
			if($status == 1) $status[1] = "selected";
			else $status[2] = "selected";
			$where[] = "e.read_mess={$status_mess}";
		}
		else
		{
			$status[0] = "selected";
		}
	}
	if(isset($where) && is_array($where) && !empty($where))
	{
		$where = "WHERE " . implode(" AND ", $where);
	}

	$sql_count = $db->super_query("SELECT COUNT(*) as count FROM ". PREFIX . "_ene_pm e {$where}");
	$all_count_news = $sql_count['count'];
	if ( intval($_REQUEST['news_per_page']) > 0 )
		$news_per_page = intval( $_REQUEST['news_per_page'] );
	else
		$news_per_page = 25;
	
	if(isset($_REQUEST['start_from']) && $_REQUEST['start_from'])
		$start_from = intval( $_REQUEST['start_from'] );
	else
	{
		if (!isset($cstart) or ($cstart<1))
		{
			$cstart = 1;
			$start_from = 0;
		}
		else
			$start_from = ($cstart-1)*$news_per_page;
	}
	$i = $start_from;
	$sql_message = $db->query("SELECT e.id as eid, e.user_id as euid, e.from_user_id, e.text, e.date, e.read_mess, u.* FROM " . PREFIX . "_ene_pm e LEFT JOIN " . PREFIX . "_users u ON (e.from_user_id=u.user_id) {$where} ORDER BY date DESC LIMIT $start_from,$news_per_page");
	$row_count = $sql_message->num_rows;
echo <<<HTML
<div style="display:none" name="advancedsearch" id="advancedsearch">
<form class="form-horizontal" action="" method="GET" name="optionsbar" id="optionsbar">
	<input type="hidden" name="mod" value="ene_pm">
	<input type="hidden" name="action" value="messages">
	<div class="box">
		<div class="box-header">
			<div class="title">{$ene_pm_lang['all_count_pm']} {$all_count_news}</div>
		</div>
		<div class="box-content">
			<div class="row box-section">
				<div class="col-md-12">
					<div class="form-group">
						<label class="control-label col-lg-5">{$ene_pm_lang['search_from']}</label>
						<div class="col-lg-7">
							<input name="search_who" value="{$search_who}" type="text" style="width:100%" >
						</div>
					</div>
					
					<div class="form-group">
						<label class="control-label col-lg-5">{$ene_pm_lang['search_to']}</label>
						<div class="col-lg-7">
							<input name="search_which" value="{$search_which}" type="text" style="width:100%" >
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-lg-3">{$ene_pm_lang['from_date']}</label>
						<div class="col-lg-3">
							<input data-rel="calendar" type="text" value="{$from_date}" name="from_date" size="20">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-lg-3">{$ene_pm_lang['to_date']}</label>
						<div class="col-lg-3">
							<input data-rel="calendar" type="text" name="to_date" value="{$to_date}" size="20">
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-lg-3">{$ene_pm_lang['pm_status']}</label>
						<div class="col-lg-9">
							<select class="uniform" name="status" id="news_status">
								<option {$status['0']} value="2">{$ene_pm_lang['status_1']}</option>
								<option {$status['1']} value="1">{$ene_pm_lang['status_2']}</option>
								<option {$status['2']} value="0">{$ene_pm_lang['status_3']}</option>
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row box-section">
			<button type="submit" class="btn btn-blue"><i class="icon-search"></i> {$ene_pm_lang['search_text']}</button>
		</div>
		
	</div>
</form>
</div>
HTML;
if($row_count > 0)
{
	while( $row = $db->get_row($sql_message) )
	{
		$i++;
		$to_user = $db->super_query("SELECT name FROM " . PREFIX . "_users WHERE user_id='{$row['euid']}'");
		$row["text"] = stripslashes(htmlspecialchars_decode($row["text"]));
		if($row["read_mess"] == 1)
			$read_mess = "<span style=\"color:green!important;\">{$ene_pm_lang['yes']}</span>";
		else
			$read_mess = "<span style=\"color:red!important;\">{$ene_pm_lang['no']}</span>";
$list .= <<< HTML
	<tr>
		<td>{$row['name']}</td>
		<td>{$to_user['name']}</td>
		<td>{$row['text']}</td>
		<td style="text-align:center;">{$row['date']}</td>
		<td style="text-align:center;">{$read_mess}</td>
		<td style="text-align:center;">
			<input type="checkbox" name="eid[]" value="{$row['eid']}" />
		</td>
	</tr>
HTML;
		$db->free($to_user);
	}

echo <<< HTML
<script type="text/javascript">
<!--
function ckeck_uncheck_all() {
    var frm = document.ene_pm;
    for (var i=0;i<frm.elements.length;i++) {
        var elmnt = frm.elements[i];
        if (elmnt.type=='checkbox') {
            if(frm.master_box.checked == true){ elmnt.checked=false; }
            else{ elmnt.checked=true; }
        }
    }
    if(frm.master_box.checked == true){ frm.master_box.checked = false; }
    else{ frm.master_box.checked = true; }
}
-->
</script>
<style>
	.emoji
	{
		width: 16px!important;
		height: 16px!important;
	}
</style>
<form name="ene_pm" action="" method="post">
	<div class="box">
		<div class="box-header">
			<div id="newstitlelist" class="title">{$ene_pm_lang['pm_list']}</div>
			<ul class="box-toolbar">
				<li class="toolbar-link">
					<a href="javascript:ShowOrHide('advancedsearch');"><i class="icon-search"></i> {$ene_pm_lang['search_show']}</a>
				</li>
			</ul>
		</div>
		<div class="box-content">
			<table class="table table-normal table-hover">
				<thead>
					<tr>
						<td>{$ene_pm_lang['from']}</td>
						<td>{$ene_pm_lang['to']}</td>
						<td>{$ene_pm_lang['text']}</td>
						<td>{$ene_pm_lang['date']}</td>
						<td>{$ene_pm_lang['read']}</td>
						<td style="width: 40px"><input type="checkbox" name="master_box" title="{$ene_pm_lang['check_all']}" onclick="javascript:ckeck_uncheck_all();"></td>
					</tr>
				</thead>
				<tbody>
					{$list}
				</tbody>
			</table>
	   </div>
		<div class="box-footer padded">
			<div class="pull-right">
				<select name="delete" class="uniform">
					<option value="">{$lang['edit_selact']}</option>
					<option value="delete">{$lang['edit_seldel']}</option>
				</select>
				&nbsp;<input class="btn btn-gold" type="submit" value="{$lang['b_start']}">
			</div>
		</div>
	</div>
	<input type="hidden" name="mod" value="ene_pm" />
	<input type="hidden" name="action" value="messages" />
	<input type="hidden" name="do" value="delete" />
</form>
HTML;
	}
	else
	{
echo <<<HTML
<div class="box">
	<div class="box-header">
		<div id="newstitlelist" class="title">{$lang['news_list']}</div>
		<ul class="box-toolbar">
			<li class="toolbar-link">
				<a href="javascript:ShowOrHide('advancedsearch');"><i class="icon-search"></i> {$lang['news_advanced_search']}</a>
			</li>
		</ul>
	</div>
	<div class="box-content">
		<div class="row box-section" style="display: table;min-height:100px;">
			<div class="col-md-12 text-center" style="display: table-cell;vertical-align:middle;">{$lang['edit_nonews']}</div>
		</div>
	</div>
</div>
HTML;
	}
if( $all_count_news > $news_per_page )
{
	if( $start_from > 0 )
	{
		$previous = $start_from - $news_per_page;
		$npp_nav .= "<li><a href=\"$PHP_SELF?mod=ene_pm&amp;action=messages&amp;start_from=$previous&amp;news_per_page=$news_per_page\"> &lt;&lt; </a></li>";
	}
	
	$enpages_count = @ceil( $all_count_news / $news_per_page );
	$enpages_start_from = 0;
	$enpages = "";

	if( $enpages_count <= 10 )
	{
		for($j = 1; $j <= $enpages_count; $j ++)
		{
			if( $enpages_start_from != $start_from )
				$enpages .= "<li><a href=\"$PHP_SELF?mod=ene_pm&amp;action=messages&amp;start_from=$enpages_start_from&amp;news_per_page=$news_per_page\">$j</a></li>";
			else
				$enpages .= "<li class=\"active\"><span>$j</span></li>";
			$enpages_start_from += $news_per_page;
		}
		$npp_nav .= $enpages;

	}
	else
	{
		$start = 1;
		$end = 10;
		if( $start_from > 0 )
		{
			if( ($start_from / $news_per_page) > 4 )
			{
				$start = @ceil( $start_from / $news_per_page ) - 3;
				$end = $start + 9;
				if( $end > $enpages_count )
				{
					$start = $enpages_count - 10;
					$end = $enpages_count - 1;
				}
				$enpages_start_from = ($start - 1) * $news_per_page;
			}
		}

		if( $start > 2 )
			$enpages .= "<li><a href=\"#\">1</a></li> <li><span>...</span></li>";

		for($j = $start; $j <= $end; $j ++)
		{
			if( $enpages_start_from != $start_from )
				$enpages .= "<li><a href=\"$PHP_SELF?mod=ene_pm&amp;action=messages&amp;start_from=$enpages_start_from&amp;news_per_page=$news_per_page\">$j</a></li>";
			else
				$enpages .= "<li class=\"active\"><span>$j</span></li>";
			$enpages_start_from += $news_per_page;
		}
		$enpages_start_from = ($enpages_count - 1) * $news_per_page;
		$enpages .= "<li><span>...</span></li><li><a href=\"$PHP_SELF?mod=ene_pm&amp;action=messages&amp;start_from=$enpages_start_from&amp;news_per_page=$news_per_page\">$enpages_count</a></li>";
		$npp_nav .= $enpages;
	}

	if( $all_count_news > $i )
	{
		$how_next = $all_count_news - $i;
		if( $how_next > $news_per_page )
			$how_next = $news_per_page;
		$npp_nav .= "<li><a href=\"$PHP_SELF?mod=ene_pm&amp;action=messages&amp;start_from=$i&amp;news_per_page=$news_per_page\"> &gt;&gt; </a></li>";
	}
	$npp_nav = "<ul class=\"pagination pagination-sm\">".$npp_nav."</ul>";
}
echo <<< HTML
	<div class="box-footer padded">
		<div class="pull-left">{$npp_nav}</div>
	</div>
	<center><b>by Gameer</b> - <a href="http://gameer.name">Gameer.name</a></center><br /><br />
HTML;
echofooter();
}
}
elseif($action == "settings")
{
echoheader("<i class=\"icon-envelope\"></i>" . $ene_pm_lang['title'] . " " . $ene_pm_lang["ver_txt"] . $ene_pm_lang["ver"], $ene_pm_lang['settings']);
echo <<<HTML
	<form action="" method="post">
		<div id="setting" class="box">
			<div class="box-header"><div class="title">{$ene_pm_lang['settings']}</div></div>
			<div class="box-content">
				<table class="table table-normal">
HTML;
	showRow($ene_pm_lang["settings_status"], $ene_pm_lang["settings_status_descr"], makeCheckBox("save_con[status]", $ene_pm_cfg["status"]));
	showRow($ene_pm_lang["settings_online"], $ene_pm_lang["settings_online_descr"], makeCheckBox("save_con[online]", $ene_pm_cfg["online"]));
	showRow($ene_pm_lang["settings_notife"], $ene_pm_lang["settings_notife_descr"], makeCheckBox("save_con[notife]", $ene_pm_cfg["notife"]));
	showRow($ene_pm_lang["settings_counts"], $ene_pm_lang["settings_counts_descr"], showNumberIs("save_con[counts]", $ene_pm_cfg["counts"]));
	showRow($ene_pm_lang["settings_blocks"], $ene_pm_lang["settings_blocks_descr"], showNumberIs("save_con[blocks]", $ene_pm_cfg["blocks"]));
	showRow($ene_pm_lang["settings_message"],$ene_pm_lang["settings_message_descr"],showNumberIm("save_con[message]",$ene_pm_cfg["message"]));
echo <<<HTML
				</table>
			</div>
			<div class="box-footer padded">
				<input type="submit" class="btn btn-lg btn-green" value="{$lang['user_save']}">
			</div>
		</div>
		<input type="hidden" name="action" value="save" />
		<input type="hidden" name="mod" value="ene_pm" />
	</form>
HTML;
echofooter();
}
else
{
echoheader("<i class=\"icon-envelope\"></i>" . $ene_pm_lang['title'] . " " . $ene_pm_lang["ver_txt"] . $ene_pm_lang["ver"], $ene_pm_lang['main']);
$data_table = $db->super_query("SHOW TABLE STATUS WHERE Name='".USERPREFIX."_ene_pm';");
$kb_database = round(($data_table["Data_length"] + $data_table["Index_length"]) / 1024, 2);
$messages_count = intval($data_table["Rows"]);
if($ene_pm_cfg["status"] == 1)
	$status = $ene_pm_lang["on"];
else
	$status = $ene_pm_lang["off"];
echo <<<HTML
<div class="box">
	<div class="box-header">
		<div class="title">{$ene_pm_lang['menu']}</div>
	</div>
	<div class="box-content">
		<div class="row box-section">
			<div class="col-md-6">
				<div class="news with-icons">
					<div class="avatar"><img src="engine/skins/images/ene_pm_mess.png"></div>
					<div class="news-content">
						<div class="news-title"><a href="?mod=ene_pm&amp;action=messages">{$ene_pm_lang['message_title_menu']}</a></div>
						<div class="news-text">
							<a href="?mod=ene_pm&amp;action=messages">{$ene_pm_lang['message_descr_menu']}</a>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="news with-icons">
					<div class="avatar"><img src="engine/skins/images/ene_pm_settings.png"></div>
					<div class="news-content">
						<div class="news-title"><a href="?mod=ene_pm&amp;action=settings">{$ene_pm_lang['settings_title_menu']}</a></div>
						<div class="news-text">
							<a href="?mod=ene_pm&amp;action=settings">{$ene_pm_lang['settings_descr_menu']}</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="box">
			<div class="tab-pane" id="statauto" >
				<table class="table table-normal">
					<tr>
						<td class="col-md-3 white-line">{$ene_pm_lang['status']}</td>
						<td class="col-md-9 white-line">{$status}</td>
					</tr>
					<tr>
						<td class="col-md-3 white-line">{$ene_pm_lang['ver_text']}</td>
						<td class="col-md-9 white-line">{$ene_pm_lang['ver']}</td>
					</tr>
					<tr>
						<td>{$ene_pm_lang['size_table']}</td>
						<td>{$kb_database} kB</td>
					</tr>
					<tr>
						<td>{$ene_pm_lang['count_message']}</td>
						<td>{$messages_count}</td>
					</tr>
				</table>      
			</div>
		 </div>
	 </div>		 
</div>
HTML;
echofooter();
}
?>