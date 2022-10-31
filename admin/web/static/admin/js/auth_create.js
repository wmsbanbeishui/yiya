$(document).ready(function() {
	var eles = getItem(menus);
	$(".menus").html(eles);
	if(type == "create") {
		$(".menus .ul_parent li ul").hide();
		$(".menus .ul_parent .i_has_child_active").addClass("i_has_child");
	}
	//ul点击，点左边展开或者闭合，点右边，全选或者全不选
	$('.menus .p-top-title span:last-child').click(function() {
		if(type && type == "look") {
			return;
		}
		if($(this).hasClass("active")) {
			$(this).removeClass("active");
		} else {
			$(this).addClass("active");
			//描述自动赋值
			var active_num = $(".p-top-title span.active").length;
			var dis_val = $("#input-discrib").val();
			if(active_num == 1 && (dis_val.indexOf("/") == 0 || dis_val == "")) {
				var value = $(this).prev('span').html();
				var uri = $('#input-name').val();
				value = value.replace('管理', '');
				if(uri.substr(-7) == '/create') {
					value = '添加' + value;
				} else if(uri.substr(-7) == '/delete') {
					value = '删除' + value;
				} else if(uri.substr(-6) == '/index') {
					value = '管理' + value;
				} else if(uri.substr(-7) == '/update') {
					value = '修改' + value;
				} else if(uri.substr(-5) == '/view') {
					value = '查看' + value;
				}
				$("#input-discrib").val(value);
			}
		}
		return false;
	})
	$('.menus .p-top-title').click(function() {
		if($(this).children("i").hasClass("i_has_child_active")) {
			if($(this).parent().children('ul').is(":hidden")) {
				$(this).children("i").removeClass("i_has_child");
			} else {
				$(this).children("i").addClass("i_has_child");
			}
		}
		$(this).parent().children("ul").slideToggle(200);
		return false;
	})
	$(".submit-sure button").click(function() {
		submitInfo();
	})
	//是否要全部展开/折叠按钮
	$(".menus").before('<p class="all-control"><span>全部展开</span><i class="active"></i></p>');
	if(type == "create") {
		$(".all-control i").removeClass("active");
	}
	$(".all-control").click(function() {
		if($(this).children("i").hasClass("active")) {
			$(this).children("i").removeClass("active");
			$(".menus .ul_parent li ul").hide();
			$(".menus .ul_parent .i_has_child_active").addClass("i_has_child");
		} else {
			$(this).children("i").addClass("active");
			$(".menus .ul_parent li ul").show();
			$(".menus .ul_parent .i_has_child_active").removeClass("i_has_child");
		}
	})
})
var _html = "";

function getItem(childs) {
	_html += "<ul class='ul_parent'>";
	for(var i = 0; i < childs.length; i++) {
		var str_choose = childs[i].checked ? "active" : "";
		var i_choose = "";
		if(childs[i].children && childs[i].children.length > 0) {
			i_choose = "i_has_child_active";
		}
		var _name = "";
		var _describtion = "";
		_name = childs[i].name;
		if(childs[i].description) {
			_describtion = childs[i].description;
		} else {
			_describtion = _name;
			_name = "";
		}
		_html += "<li><p class='p-top-title'  data-id=" + childs[i].id + "><i class=" + i_choose + "></i><span>" + _describtion + "</span><span class=" + str_choose + "></span></p>";
		if(childs[i].children && childs[i].children.length > 0) {
			getItem(childs[i].children);
			_html += "</li>";
			continue;
		}
		_html += "</li>";
	}
	_html += "</ul>";
	return _html;
}

function submitInfo() {
	var sendData = {};
	sendData.type = 2;
	var name = $("#input-name").val();
	var discrib = $("#input-discrib").val();
	var arr = [];
	$(".p-top-title").each(function() {
		if($(this).children("span:last-of-type").hasClass("active")) {
			if($(this).data('id')) {
				arr.push($(this).data('id'));
			}
		}
	})
	sendData.menu_ids = arr;
	if(arr.length < 1) {
		VCT.Toast("请选择菜单", 2000);
		return;
	}
	if(!name) {
		VCT.Toast("请输入名字", 2000);
		return;
	}
	if(!discrib) {
		VCT.Toast("请输入描述", 2000);
		return;
	}
	if(id) {
		sendData.id = id;
	}
	sendData.name = name;
	sendData.description = discrib;

	$.ajax({
		url: '/auth-item/save-auth',
		type: 'post',
		data: sendData,
		dataType: 'json',
		success: function(data) {
			VCT.Toast(data.msg, 2000);
			if(data.errno == 0) {
				history.go(-1);
			}
		}
	})
}