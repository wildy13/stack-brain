function RenderTopic() {
	var AllPosts = document.getElementsByClassName("comment-content");
	PostContentLists = {};//Global
	AllPosts[AllPosts.length] = document.getElementsByClassName("topic-content")[0];
	if (document.getElementsByClassName("topic-content").length > 0) {
		PostContentLists[document.getElementsByClassName("topic-content")[0].childNodes[1].id] = trim3(document.getElementsByClassName("topic-content")[0].childNodes[1].innerHTML);
	}
	//console.log(PostContentLists);
	for (var j = 0; j < AllPosts.length; j++) {
		PostContentLists[document.getElementsByClassName("comment-content")[j].childNodes[5].id] = trim3(document.getElementsByClassName("comment-content")[j].childNodes[5].innerHTML);
		//console.log(PostContentLists);
		var AllLinks = AllPosts[j].getElementsByTagName("a");
		for (var i = 0; i < AllLinks.length; i++) {
			var a = AllLinks[i];
			//console.log(a);
			if (a.host !== location.host || a.href.indexOf("upload/") !== -1) {
				a.setAttribute("target", "_blank");
			}
		}
	}
	uParse('.topic-content', {
		'rootPath': WebsitePath + '/static/editor/',
		'liiconpath': WebsitePath + '/static/editor/themes/ueditor-list/'
	});
	uParse('.comment-content', {
		'rootPath': WebsitePath + '/static/editor/',
		'liiconpath': WebsitePath + '/static/editor/themes/ueditor-list/'
	});

	if (TopicID) {
		var postA = $('a[href*="#Post"]');
		var posts = {};
		var tip = $("#reply-mouse-tip");
		var tipAuthor = tip.find("a.author");
		var tipContent = tip.find("div.content");
		var showTip = function (ele, data) {
			if (!data) {
				$("#reply-mouse-tip").hide();
				return false;
			}
			tipAuthor.text(data.UserName);
			tipAuthor.attr("href", WebsitePath + "/u/" + data.UserName);
			tipContent.html(data.Content);
		};
		var attach = function (ele, postId) {
			ele.hover(function () {
				var pos = ele.position();
				pos.top += ele.height();
				tip.css(pos).show();
				tipAuthor.text("");
				tipContent.text("Loading...");
				if (postId in posts) {
					showTip(ele, posts[postId]);
				} else {
					$.post(WebsitePath + "/json/get_post", {PostId: postId})
						.success(function (data) {
							posts[postId] = data;
							showTip(ele, data);
						});
				}
			}, function () {
				$("#reply-mouse-tip").hide();
			});
		};
		for (var index = 0; index < postA.length; index++) {
			var $element = $(postA[index]);
			var postId = $element.attr("href").match(/#Post([0-9]+)/);
			if (postId) {
				postId = postId[1];
				attach($element, postId);
			}
		}
	}
}

function InitEditor() {
	//Initialize editor
	UE.delEditor('editor');
	window.UEDITOR_CONFIG['textarea'] = 'Content';
	window.UEDITOR_CONFIG['elementPathEnabled'] = false;
	window.UEDITOR_CONFIG['toolbars'] = [
		[
			'fullscreen',
			'source',
			'|',
			'undo',
			'redo',
			'|',
			'bold',
			'italic',
			'underline',
			'strikethrough', 
			'forecolor', 
			'backcolor', 
			'paragraph',
			'fontsize',
			'fontfamily'
		],
		[
			'insertcode',
			'link',
			'blockquote',
			'insertorderedlist',
			'insertunorderedlist',
			'|',
			'emotion',
			'simpleupload',
			'insertimage',
			'scrawl',
			'insertvideo',
			//'music',
			'attachment',
			'map', 
			'gmap',
			'|',
			'inserttable',
			'insertrow', 
			'insertcol', 
			'|',
			'searchreplace',
			'template', 
			'autotypeset'
		]
	];
	UE.getEditor('editor', {
		onready: function () {
			if (window.localStorage) {
				if (typeof SaveDraftTimer !== "undefined") {
					clearInterval(SaveDraftTimer);
					console.log('StopTopicAutoSave');
				}
				if (typeof SavePostDraftTimer !== "undefined") {
					clearInterval(SavePostDraftTimer);
					console.log('StopAutoSave');
				}
				//Try to recover previous article from draft
				RecoverContents();
				SavePostDraftTimer = setInterval(function () {//Global
						SavePostDraft();
					},
					1000); 

			}
			//Press Ctrl + Enter to submit in editor
			var EditorIframe = document.getElementsByTagName("iframe");
			//console.log(EditorIframe);
			for (var i = EditorIframe.length - 1; i >= 0; i--) {
				EditorIframe[i].contentWindow.document.body.onkeydown = function (Event) {
					ReplyCtrlAndEnter(Event);
				};
				//console.log(EditorIframe[i].contentWindow.document);
			}
		}
	});
	document.body.onkeydown = function (Event) {
		ReplyCtrlAndEnter(Event);
	};
	console.log('editor loaded.');
}


function ReplyCtrlAndEnter(Event) {
	//console.log("keydown");
	if (Event.ctrlKey && Event.keyCode === 13) {
		$("#ReplyButton").click();
		Event.preventDefault ? Event.preventDefault() : Event.returnValue = false;//阻止回车的默认操作
	}
}


function trim3(str) {
	if (str) {
		str = str.replace(/^(\s|\u00A0)+/, '');
		for (var i = str.length - 1; i >= 0; i--) {
			if (/\S/.test(str.charAt(i))) {
				str = str.substring(0, i + 1);
				break;
			}
		}
	}
	return str;
}

function InitNewTagsEditor() {
	$("#AlternativeTag").keydown(function (e) {
		var e = e || event;
		switch (e.keyCode) {
			case 13:
				if ($("#AlternativeTag").val().length !== 0) {
					$.ajax({
						url: WebsitePath + "/manage",
						data: {
							ID: TopicID,
							Type: 1,
							Action: 'AddTag',
							TagName: $("#AlternativeTag").val()
						},
						cache: false,
						dataType: "json",
						type: "POST",
						success: function (Data) {
							if (Data.Status === 1) {
								$("#TagsElements").append('<a href="' + WebsitePath + '/tag/' + $("#AlternativeTag").val() + '" id="Tag' + md5($("#AlternativeTag").val()) + '">' + $("#AlternativeTag").val() + '</a>');
								$("#EditTagsElements").append('<a href="###"  onclick="javascript:DeleteTag(' + TopicID + ', this, \'' + $("#AlternativeTag").val() + '\');">' + $("#AlternativeTag").val() + ' ×</a>');
							}
							$("#AlternativeTag").val("");
						}
					});
				}
				break;
			default:
				return true;
		}
	});
}


function EditTags() {
	$("#TagsList").hide();
	$("#EditTags").show();
}

function CompletedEditingTags() {
	$("#EditTags").hide();
	$("#TagsList").show();
}

function DeleteTagCallback(TargetTag, TagName) {
	this.Success = function (Json) {
		if (Json.Status === 1) {
			$(TargetTag).remove();
			$("#Tag" + md5(TagName)).remove();
		} else {
			$(TargetTag).text(TagName + " ×");
		}
	};
}

function DeleteTag(TopicID, TargetTag, TagName) {
	$(TargetTag).text("Loading");
	var CallbackObj = new DeleteTagCallback(TargetTag, TagName);
	$.ajax({
		url: WebsitePath + "/manage",
		data: {
			ID: TopicID,
			Type: 1,
			Action: 'DeleteTag',
			TagName: TagName
		},
		cache: false,
		dataType: "json",
		type: "POST",
		success: CallbackObj.Success
	});
}


function EditPost(PostID) {
	//document.getElementById('p' + PostID).style.visibility = "hidden";
	//document.getElementById('p' + PostID).style.height = "0";
	$("#p" + PostID).hide();
	window.UEDITOR_CONFIG['textarea'] = 'PostContent' + PostID;
	UE.getEditor('edit' + PostID, {
		onready: function () {
			UE.getEditor('edit' + PostID).setContent(PostContentLists['p' + PostID]); //将帖子内容放到编辑器里
		}
	});
	$("#edit" + PostID).show();
	if ($("#edit_button" + PostID) && $("#edit_button" + PostID).length == 0) {
		$("#edit" + PostID).append('<div id="edit_button' + PostID + '"><p></p><p><input type="button" value=" ' + Lang['Edit'] + ' " class="textbtn" id="EditButton' + PostID + '" onclick="JavaScript:SubmitEdit(' + PostID + ');">&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value=" ' + Lang['Cancel'] + ' " class="textbtn" onclick="JavaScript:DestoryEditor(' + PostID + ');"></p>');
	}
	//document.getElementById('edit' + PostID).style.visibility = "visible";
}

function DestoryEditor(PostID) {
	UE.getEditor('edit' + PostID).destroy();
	$("#p" + PostID).show();
	//document.getElementById('p' + PostID).style.visibility = "visible";
	//document.getElementById('p' + PostID).style.height = "auto";
	$("#edit" + PostID).html("");
	$("#edit" + PostID).hide();
	//document.getElementById('edit' + PostID).style.height = "0";
	//document.getElementById('edit' + PostID).style.padding = "0";
	//document.getElementById('edit' + PostID).style.visibility = "hidden";
}

function SubmitEdit(PostID) {
	var EditCallbackObj = new EditPostCallback(PostID);
	$.ajax({
		url: WebsitePath + "/manage",
		data: {
			ID: PostID,
			Type: 2,
			Action: 'Edit',
			Content: UE.getEditor('edit' + PostID).getContent()
		},
		cache: false,
		dataType: "json",
		type: "POST",
		success: EditCallbackObj.Success
	});

}

function EditPostCallback(PostID) {
	this.Success = function (Json) {
		if (Json.Status === 1) {
			document.getElementById('p' + PostID).innerHTML = UE.getEditor('edit' + PostID).getContent();
			PostContentLists['p' + PostID] = UE.getEditor('edit' + PostID).getContent();
			DestoryEditor(PostID);
			RenderTopic();
		} else {
			alert(Json.ErrorMessage);
		}
	};
}

function ReplyToTopic() {
	if (!UE.getEditor('editor').getContent().length) {
		alert(Lang['Content_Empty']);
		UE.getEditor('editor').focus();
	} else {
		$("#ReplyButton").val(Lang['Replying']);
		UE.getEditor('editor').setDisabled('fullscreen');
		$.ajax({
			url: WebsitePath + '/reply',
			data: {
				FormHash: document.reply.FormHash.value,
				TopicID: document.reply.TopicID.value,
				Content: UE.getEditor('editor').getContent()
			},
			type: 'post',
			cache: false,
			dataType: 'json',
			async: true,
			success: function (data) {
				if (data.Status === 1) {
					console.log(SavePostDraftTimer);
					$("#ReplyButton").val(Lang['Reply_Success']);
					if (window.localStorage) {
						StopAutoSave();
					}
					//UE.getEditor('editor').execCommand('cleardoc');
					console.log(SavePostDraftTimer);
					$.pjax({
						url: WebsitePath + "/t/" + data.TopicID + (data.Page > 1 ? "-" + data.Page : "") + "?cache=" + Math.round(new Date().getTime() / 1000) + "#Post" + data.PostID,
						container: '#main',
						scrollTo: false
					});
					//location.href = WebsitePath + "/t/" + data.TopicID + (data.Page > 1 ? "-" + data.Page: "") + "?cache=" + Math.round(new Date().getTime() / 1000) + "#Post" + data.PostID;
				} else {
					alert(data.ErrorMessage);
					UE.getEditor('editor').setEnabled();
					$("#ReplyButton").val(Lang['Submit_Again']);
				}
			},
			error: function () {
				alert(Lang['Submit_Failure']);
				UE.getEditor('editor').setEnabled();
				$("#ReplyButton").val(Lang['Submit_Again']);
			}
		});
	}
	return true;
}


function Reply(UserName, PostFloor, PostID) {
	UE.getEditor('editor').setContent('<p>' + Lang['Reply_To'] + '<a href="' + location.pathname + '#Post' + PostID + '">#' + PostFloor + '</a> @' + UserName + ' :<br /></p><p></p>', false);
	UE.getEditor('editor').focus(true);
}

function Quote(UserName, PostFloor, PostID) {
	UE.getEditor('editor').setContent('<p></p><blockquote><a href="' + location.pathname + '#Post' + PostID + '">#' + PostFloor + '</a> @' + UserName + ' :<br />' + PostContentLists['p' + PostID] + '<blockquote>');
	UE.getEditor('editor').focus(false);
}

//Save Draft
function SavePostDraft() {
	try {
		if (UE.getEditor('editor').getContent().length >= 10) {
			localStorage.setItem(Prefix + "PostContent" + TopicID, UE.getEditor('editor').getContent());
		}
	} catch (oException) {
		if (oException.name === 'QuotaExceededError') {
			console.log('Draft Overflow! ');
			localStorage.clear();//Clear all draft
			SavePostDraft();//Save draft again
		}
	}
}

function StopAutoSave() {
	clearInterval(SavePostDraftTimer); 
	localStorage.removeItem(Prefix + "PostContent" + TopicID);
	UE.getEditor('editor').execCommand("clearlocaldata"); 
}

function RecoverContents() {
	var DraftContent = localStorage.getItem(Prefix + "PostContent" + TopicID);
	if (DraftContent) {
		UE.getEditor('editor').setContent(DraftContent);
	} else {
		UE.getEditor('editor').execCommand('cleardoc');
	}
}