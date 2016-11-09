[block]
<div class="fc_tab_wrap draggable chatblocked" id="eneblock_{id}" data-pmall="{allpm}" data-from="15" data-pmunread="{unread}" data-chatblock="{id}" data-time="{last-time}">
	<div class="fc_tab_head clear_fix" id="afteridoon_{id}">
		<a class="fc_tab_close_wrap" onclick="HideDialog({id});return false;"><div class="chats_sp fc_tab_close"></div></a>
		<div class="fc_tab_title noselect" style="max-width: 199px;">{nick}</div>
	</div>
	[offline]<div class="fc_tab_notify fc_tab_notify_unavail" id="inchatonline_{id}">{nick} сейчас не в сети</div>[/offline]
	<div class="fc_tab">
		<div class="fc_tab_log_wrap">
			<div class="fc_tab_log" id="beforeoff_{id}" style="height: 299px; overflow: hidden;">
				<div class="fc_tab_log_msgs scroller_{id}" id="allmessage_{id}">
[/block]
					<div id="putmess_{id}">
					[last_message]
						{message}
					[/last_message]
					</div>
					[last_message]
					[/last_message]
[block]
				</div>
			</div>
		</div>
		<div class="fc_tab_txt_wrap">
			<div class="fc_tab_txt">
				<div class="emoji_cont _emoji_field_wrap">
					<div class="emoji_smile_wrap _emoji_wrap">
						<div class="emoji_smile _emoji_btn" onmouseout="HideSmileP({id});return false;" onmouseover="ShowSmileP({id});return false;">
							<div class="emoji_smile_icon"></div>
							<div id="emoji_block_{id}"  class="emoji_tt_wrap tt_down emoji_expanded" style="opacity: 0; display: none;">
								<div class="emoji_block_cont">
									<div class="emoji_block_rel">
										<div class="emoji_list_cont">
											<div class="emoji_list scrollersd{id}"  style="height: 242px; overflow: hidden;">
												<div class="emoji_scroll">
													{emoji}
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="fc_editable dark" tabindex="0" contenteditable="true" id="enepmtext_{id}" data-messid="{id}"></div>
					<div class="placeholder" id="placeholder_{id}">
						<div class="ph_input">
							<div class="ph_content">Ваше сообщение…</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
[/block]