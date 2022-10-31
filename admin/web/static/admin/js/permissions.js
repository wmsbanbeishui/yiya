$(document).ready(function() {
	var eles = getItem(menus);
	$(".menus").html(eles);
	$(".menus ul li p").each(function() {
		var name = $(this).data("name");
		if(name) {
			$(this).addClass("permission");
		}
	});
	//ul点击，点左边展开或者闭合，点右边，全选或者全不选
	$('.menus .p-top-title span:last-child').click(function() {
		if($(this).hasClass("active")) {
			//子项全变成未选中
			$(this).parent().parent().find("span:last-child").removeClass("active");
			//属性名相同，子选项也变成未选中
			var _router = $(this).parent().attr('router');
			if(_router) {
				var obj = $('p[router="' + _router + '"]');
				obj.parent().find("span:last-child").removeClass("active");
			}

		} else {
			//子项全变成选中
			$(this).parent().parent().find("span:last-child").addClass("active");
			$(this).parent().parent().children("ul").slideDown(200);
			//属性名相同，子选项也变成选中
			var _router = $(this).parent().attr('router');
			if(_router) {
				var obj = $('p[router="' + _router + '"]');
				obj.parent().find("span:last-child").addClass("active");
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
		var _router = "";
		_name = childs[i].name;
		_router = childs[i].name;
		if(childs[i].description) {
			_describtion = childs[i].description;
		} else {
			_describtion = _name;
			_name = "";
		}
		_html += "<li><p class='p-top-title' router=" + _router + " data-name=" + _name + "><i class=" + i_choose + "></i><span>" + _describtion + "</span><span class=" + str_choose + "></span></p>";
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
	sendData.role = role;
	var arr = [];
	$(".p-top-title").each(function() {
		if($(this).children("span:last-of-type").hasClass("active")) {
			if($(this).data('name')) {
				arr.push($(this).data('name'));
			}
		}
	})
	sendData.prem = arr;

	$.ajax({
		url: '/role/list-do',
		type: 'post',
		data: sendData,
		dataType: 'json',
		success: function(data) {
			VCT.Toast(data.msg, 2000);
		}
	})
}