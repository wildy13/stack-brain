function CheckUserName() {
	if ($("#UserName").val() && $("#UserName").val().length >= 4 && $("#UserName").val().length <= 20) {
		$.ajax({
			url: WebsitePath + '/json/user_exist',
			data: {
				UserName: $("#UserName").val()
			},
			type: 'post',
			dataType: 'json',
			success: function(Json) {
				if (Json.Status == 1) {
					$("#UserName").addClass("inputnotice");
				} else {
					$("#UserName").removeClass("inputnotice");
				}
			}
		});
	} else {
		$("#UserName").addClass("inputnotice");
	}
}

function CheckPassword() {
	if ($("#Password").val().length < 6){
		$("#Password").addClass("inputnotice");
	}else{
		$("#Password").removeClass("inputnotice");
	}
}

function CheckMail() {
	var EmailReg = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
	if ($("#Email").val().length >= 5 && EmailReg.test($("#Email").val())) {
		$("#Email").removeClass("inputnotice");
	} else {
		$("#Email").addClass("inputnotice");
	}
}