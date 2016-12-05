// Ene PM by Gameer - gameer.name
var audio = new Audio();
audio.src = '/uploads/new_msg.mp3';
audio.load();
var lcpm = [];
var dialogblockarr = [];
var bindlock = false;
function ShowFindUser()
{
	if($("#fc_clist").hasClass("noneshow"))
	{
		$("#fc_clist").removeClass("noneshow");
		$("#fc_clist").addClass("thisshow");
	}
	else
	{
		$("#fc_clist").removeClass("thisshow");
		$("#fc_clist").addClass("noneshow");
	}	
}

function HideDialog(id)
{
	$('[data-chatblock="' + id + '"]').remove();
	for(var inti = 0; inti < lcpm.length; inti++)
	{
		if(lcpm[inti] == id)
			lcpm.splice(inti,1);
	}
}

function ReadMess(id)
{
	if($("#eneblock_" + id).attr("data-pmunread") == 0)
	{
		$.ajax({
			type: 'POST',
			url: "/engine/modules/ene_pm/ajax/read_mess.php",
			dataType: 'json',
			data: ({
				user_id: id
			}),
			success: function(obj)
			{
				$("#unreadmessage"+id).remove();
				$("#eneblock_" + id).attr("data-pmunread",1);
				console.log(obj["suc"]);
			}
		});
	}
}

function ShowChatUser(id)
{
	if($.inArray(id, lcpm) == -1)
		lcpm.push(id);
	if ($('.chatblocked').length < 4)
	{
		if(!$('[data-chatblock="' + id + '"]').length)
		{
			$.ajax({
                type: 'GET',
                url: "/engine/modules/ene_pm/ajax/user_chat.php",
                dataType: 'json',
                data: ({
                    user_id: id
                }),
				success: function(obj)
				{
					$("body").append(obj["block"]);
					if($("[data-chatblock]").length > 1)
					{
						$("#eneblock_" + id).css("left", (270 * ($("[data-chatblock]").length - 1)) + 30);
					}
					var allscroll = $('#allmessage_'+ id);
					allscroll.jScrollPane();
					dialogblockarr[id] = allscroll.data('jsp');
					dialogblockarr[id].scrollToBottom();
					allscroll.bind(
							'jsp-scroll-y',
							function(event, scrollPositionY, isAtTop, isAtBottom)
							{
								if(scrollPositionY == 0 && isAtTop && bindlock)
								{
									update_msg(lcpm, true);
									dialogblockarr[id].scrollByY(15, false);
								}
							}
						);
					$('.scrollersd'+ id).jScrollPane();
					$(function() {
						$( '[data-chatblock="' + id + '"]' ).draggable({
							containment: "window",
							handle: "div.fc_tab_title",
							scroll: false,
							cursor: 'move'
						});
					});
					$("#unreadmessage"+id).remove();
					ReadMess(id);
					setInterval('update_msg(lcpm)', 5000);
				}
			});
		}
		else
		{
			DLEalert("Этот диалог уже открыт.", "Ошибка");
		}
	}
	else
	{
		DLEalert("У вас открыто макс. разрешенное кол-во диалогов", "Ошибка");
	}
}

function update_msg(id, updback)
{
	var updback = updback || false;
	if(id.length)
	{
		for(var inti = 0; inti < id.length; inti++)
		{
			if($("#eneblock_"+id[inti]).length)
			{
				if(updback)
				{
					var $allpm = $("#eneblock_" + id[inti]).attr("data-pmall");
					var $nowpm = $("#eneblock_" + id[inti]).attr("data-from");
					if($allpm > $nowpm)
					{
						if(($allpm - $nowpm) >= 15)
						{
							$nowpm = Number($nowpm) + Number(15);
						}
						else
							$nowpm = ($allpm - $nowpm) + Number($nowpm);
					}
					else return;
				}
				else if($("#eneblock_" + id[inti]).attr("data-from") > 15)
					var $nowpm = $("#eneblock_" + id[inti]).attr("data-from");
				else
					var $nowpm = 15;
				if(updback || $nowpm > 15)
					var getback = 2;
				else
					var getback = 1;
				$.ajax({
					type: 'POST',
					url: "/engine/modules/ene_pm/ajax/upd_mess.php",
					dataType: 'json',
					data: ({
						user_id: id[inti],
						ajax_upd: $nowpm,
						msfg: getback
					}),
					success: function(obj)
					{
                        if(typeof(obj['message']) != 'undefined')
						{
							$("#putmess_" + obj["id"]).html(obj["message"]);
                        }
						if($nowpm > 15)
						{
							$("#eneblock_" + obj["id"]).attr("data-from", $nowpm);
						}
						dialogblockarr[obj["id"]].reinitialise();
						if(dialogblockarr[obj["id"]].getPercentScrolledY() == 0) bindlock = true;
						else bindlock = false;
						
						$("#eneblock_" + obj["id"]).attr("data-pmall", obj["allmessagenow"]);
						
						if(typeof(obj['newmess']) != 'undefined')
						{
							audio.play();
							dialogblockarr[obj["id"]].scrollToBottom();
							$("#eneblock_" + obj["id"]).attr("data-pmunread",0);
							ReadMess(obj["id"]);
						}
						if(typeof(obj['onlineblock']) != 'undefined')
						{
							$("#inchatonline_" + obj["id"]).remove();
							if($("#yserid_" + obj["id"]).hasClass("onlinedeas")){}
							else {$("#yserid_" + obj["id"]).addClass("onlinedeas");}
						}
						else
						{
							if($("#inchatonline_"+obj["id"]).length){}
							else $("#afteridoon_" + obj["id"]).after("<div class=\"fc_tab_notify fc_tab_notify_unavail\" id=\"inchatonline_"+obj["id"]+"\">"+obj["nick"]+" сейчас не в сети</div>");
							if($("#yserid_" + obj["id"]).hasClass("onlinedeas")) $("#yserid_" + obj["id"]).removeClass("onlinedeas");
						}
					},
					error: function (XMLHttpRequest, textStatus, errorThrown) {
					  console.log("xml : " + XMLHttpRequest + " | status : " + textStatus + " | error : " + errorThrown);
					}
				});
			}
		}
	}
}

function htmlSpecialChars(string, reverse) {
    var specialChars = {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;"
        },
        x;
    if (typeof(reverse) != 'undefined') {
        reverse = [];
        for (x in specialChars) reverse.push(x);
        reverse.reverse();
        for (x = 0; x < reverse.length; x++) {
            string = string.replace(new RegExp(specialChars[reverse[x]], "g"), reverse[x]);
        }
        return string;
    }
    for (x in specialChars) string = string.replace(new RegExp(x, "g"), specialChars[x]);
    return string;
}
function ShowSmileP(id)
{
	$("#emoji_block_" + id).css("opacity", "1");
	$("#emoji_block_" + id).css("display", "block");
}

function HideSmileP(id)
{
	$("#emoji_block_" + id).css("opacity", "0");
	$("#emoji_block_" + id).css("display", "none");
}
function pasteHtmlAtCaret(html, selectPastedContent) {
    var sel, range;
    if (window.getSelection) {
        sel = window.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
            range = sel.getRangeAt(0);
            range.deleteContents();

            var el = document.createElement("div");
            el.innerHTML = html;
            var frag = document.createDocumentFragment(), node, lastNode;
            while ( (node = el.firstChild) ) {
                lastNode = frag.appendChild(node);
            }
            var firstNode = frag.firstChild;
            range.insertNode(frag);

            if (lastNode) {
                range = range.cloneRange();
                range.setStartAfter(lastNode);
                if (selectPastedContent) {
                    range.setStartBefore(firstNode);
                } else {
                    range.collapse(true);
                }
                sel.removeAllRanges();
                sel.addRange(range);
            }
        }
    } else if ( (sel = document.selection) && sel.type != "Control") {
        var originalRange = sel.createRange();
        originalRange.collapse(true);
        sel.createRange().pasteHTML(html);
    }
}
function PsSmile(id, smiled)
{
	var idtextarea = document.getElementById("enepmtext_"+id).focus();
	var smila = "<img src='"+smiled+"' class=\"emoji\">";
	pasteHtmlAtCaret(smila, idtextarea);
}
$(function()
{
	var $search_timer = false;
    var $search_text = '';
	
    $('#fc_clist_filter').attr('autocomplete', 'off');
    function EndSearch()
    {
        $('#fc_clist_filter').keyup(function() {
            var $text = $(this).val();
            if ($search_text != $text)
            {
                clearInterval($search_timer);
                $search_timer = setInterval(function() { StartSearch($text); }, 600);
            }
        });
    }
	
	function StartSearch($text)
    {
        clearInterval($search_timer);
        $.post(dle_root + "engine/modules/ene_pm/ajax/search_user.php", {q : $text}, function(data){
            if(data){
                $('#fc_contacts').empty();
                $('#fc_contacts').html(data);
            }
        });
        $search_text = $text;
    }
    EndSearch();
	
	$( '#fc_clist' ).draggable({
		containment: "window",
		handle: "div.fc_tab_title",
		scroll: false,
		cursor: 'move'
	});
	
	$("body").on("click", "[id*=enepmtext_]", function()
	{
		var $id = $(this).attr("data-messid");
		$("#placeholder_" + $id).css("display", "none");
	});
	
	$('body').on('keydown', '[id*=enepmtext_]', function(event)
	{
		if (event.which == 13)
		{
			var $this = $(this);
			var $id_mess = $(this).attr("data-messid");
			var $time = $("#eneblock_" + $id_mess).attr("data-time");
			var $text = htmlSpecialChars($.trim($($this).html()));
			$(this).html('');
			$.ajax({
                type: 'POST',
                url: "/engine/modules/ene_pm/ajax/add_message.php",
                dataType: 'json',
                data: ({
                    user_id: $id_mess,
					date_last: $time,
					text_msg: $text
                }),
				success: function(obj)
				{
					$("#putmess_" + $id_mess).append(obj["message"]);
					$("#eneblock_" + $id_mess).attr("data-time", obj["time"]);
					dialogblockarr[$id_mess].reinitialise();
					dialogblockarr[$id_mess].scrollToBottom();
					$this.html('');
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					console.log("xml : " + XMLHttpRequest + " | status : " + textStatus + " | error : " + errorThrown);
					$this.html('');
				}
			});
		}
	});
});