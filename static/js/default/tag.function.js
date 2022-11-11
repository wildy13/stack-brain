function UploadTagIcon(TagID) {
	$.upload({
		url: WebsitePath + "/manage", 
		fileName: 'TagIcon', 
		params: {
			ID: TagID,
			Type: 5,
			Action: 'UploadIcon'
		},
		dataType: 'json',
		onSend: function() {
			return true;
		},
		onComplate: function(Data) {
			if(Data.Status == 1){
				alert(Data.Message);
			}else{
				alert(Data.ErrorMessage);
			}
		}
	});
}

function EditTagDescription() {
	$("#TagDescription").hide();
	$("#EditTagDescription").show();
}

function CompletedEditingTagDescription() {
	$("#EditTagDescription").hide();
	$("#TagDescription").show();
}

function SubmitTagDescription(TagID) {
	$.ajax({
		url: WebsitePath + "/manage",
		data: {
			ID: TagID,
			Type: 5,
			Action: 'EditDescription',
			Content: $("#TagDescriptionInput").val()
		},
		cache: false,
		dataType: "json",
		type: "POST",
		success: function(Data) {
			if(Data.Status == 1){
				CompletedEditingTagDescription();
				$("#TagDescription").text($("#TagDescriptionInput").val());
			}else{
				alert(Data.ErrorMessage);
			}
		}
	});

}