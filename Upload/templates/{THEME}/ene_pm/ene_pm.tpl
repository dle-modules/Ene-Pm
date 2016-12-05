[block]
<div id="chat_onl_wrap" class="chat_onl_wrap chat_expand" style="display: block;">
	<div class="chat_onl_inner">
[/block]
[last_block]
		<div class="chat_cont_scrolling">
			<div class="chat_onl_height">
[/last_block]
			[last_message]
				{last_user}
			[/last_message]
[last_block]				
			</div>
		</div>
[/last_block]
[block]
		<div class="chat_cont_sh_top"></div>
		<div class="chat_cont_sh_bottom"></div>
		<a class="chat_tab_wrap" id="chat_tab_wrap" onclick="ShowFindUser();return false;">
			<div class="chat_onl_cont">
				<div class="chat_onl" id="chat_onl">[online_on]{online}[/online_on]</div>
			</div>
		</a>
	</div>
</div>
[/block]
[block]
<div id="fc_clist" class="fc_tab_wraps noneshow">
	<div class="fc_tab_head">
		<a class="fc_tab_close_wrap" onclick="ShowFindUser();return false;"><div class="chats_sp fc_tab_close"></div></a>
		<div class="fc_tab_title noselect">[online_on]<span>{online}</span> пользователей онлайн[/online_on][online_off]Список переписок[/online_off]</div>
	</div>
	<div id="fc_ctabs_cont">
		<div class="fc_ctab fc_ctab_active">
[/block]
[last_block]
			<div class="fc_contacts_wrap">
				<div id="fc_contacts" class="fc_contacts" style="height: 299px;">
[/last_block]
			[last_message]
				{search}
			[/last_message]
[last_block]
				</div>
			</div>
[/last_block]
[block]
			<div class="fc_clist_filter_wrap">
				<div class="chats_sp fc_clist_search_icon"></div>
				<div class="fc_clist_filter"><input type="text" class="dark" id="fc_clist_filter" placeholder="Начните вводить имя.." onclick="event.cancelBubble = true;"></div>
			</div>
		</div>
	</div>
	<div class="fc_pointer_offset" style="bottom: 28px;"><div class="fc_tab_pointer" style="margin-top: 0px;"></div></div>
</div>
[/block]