window.VCT = {}
//带有确定和取消的弹框
VCT.alert = function(title, msg, sureCallBack) {
	var title = title || "提示";
	if($(".pop-window").length <= 0) {
		var str = '<div class="pop-window">' +
			'<div class="pop-dialog">' +
			'<p class="pop-dialog-title">' + title + '</p>' +
			'<p class="pop-dialog-content">' + msg + '</p>' +
			'<p class="pop-btns">' +
			'<span class="pop-sure">确定</span>' +
			'<span class="pop-cancal">取消</span>' +
			'</p>' +
			'</div>' +
			'</div>';
		$("body").append(str);
	} else {
		$(".pop-dialog-title").html(title);
		$(".pop-dialog-content").html(msg);
	}
	$(".pop-dialog").show();
	$('.pop-dialog').parent().show();
	$('.pop-dialog .pop-cancal').unbind().on("click", function() {
		$(".pop-dialog").hide();
		$('.pop-dialog').parent().hide();
	})
	if(sureCallBack) {
		$('.pop-dialog .pop-sure').unbind().on("click", function() {
			$(".pop-dialog").hide();
			$('.pop-dialog').parent().hide();
			sureCallBack();
		})
		return false;
	}
	$('.pop-dialog .pop-sure').unbind().on("click", function() {
		$(".pop-dialog").hide();
		$('.pop-dialog').parent().hide();
	})
}

//自定义弹框
VCT.Toast = function Toast(msg, duration) {
	duration = isNaN(duration) ? 1000 : duration;
	var m = document.createElement('div');
	m.innerHTML = msg;

	m.style.cssText = "font-size:16px;padding: 10px;width:250px !important; background:#000; opacity:0.7;  color:#fff;  text-align:center; border-radius:5px; position:fixed; top:50%; left:10%;right:10%;margin:0 auto; z-index:999999;";
	document.body.appendChild(m);
	setTimeout(function() {
		var d = 0.5;
		m.style.webkitTransition = '-webkit-transform ' + d + 's ease-in, opacity ' + d + 's ease-in';
		m.style.opacity = '0';
		setTimeout(function() {
			document.body.removeChild(m)
		}, d * 1000);
	}, duration);
}
$(document).ready(function() {
	if($(".gen-pass").length > 0) {
		var _span = '<span class="getpwd" style="position: absolute;right: 16px;display: block;padding: 6px 10px;top: 2px;font-size: 13px;cursor: pointer;">生成密码</span>';
		$(".gen-pass").parent("div").append(_span);
		$(".gen-pass").parent("div").find(".getpwd").unbind().click(function() {
			var pwd_num = $(this).attr('gen-pass-len');
			var _pwd = generatePwd(pwd_num ? pwd_num : 16);
			$(".gen-pass").val(_pwd);
		})
	}
})

function generatePwd(n) {
	var arr = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z']; 
	var res = "";    
	for(var i = 0; i < (n > 61 ? 61 : n); i++) {        
		var id = Math.ceil(Math.random() * 61);        
		res += arr[id];    
	}    
	return res;
}