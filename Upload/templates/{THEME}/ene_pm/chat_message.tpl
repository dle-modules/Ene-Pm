[date]<div class="fc_msgs_date">{date}</div>[/date]
[not-me]
	<div class="fc_msgs_wrap messageeasybox ">
		<div class="fc_msgs_author">
			<a class="fc_msgs_img" alt="[fullname]{fullname}[/fullname][not_fullname]{nick}[/not_fullname]" href="/user/{nick}/" style="background-image: url({foto})"></a>
		</div>
		<div class="fc_msgs">
			<div class="fc_msg wrapped">{message}</div>
		</div>
	</div>
[/not-me]
[me]
	<div class="fc_msgs_wrap fc_msgs_out messageeasybox [not-read]fc_msgs_unread[/not-read]">
		<div class="fc_msgs_out_inner">
			<div class="fc_msgs">
				<div class="fc_msg wrapped fc_msg_last">{message}</div>
			</div>
		</div>
		<br class="clear">
	</div>
[/me]