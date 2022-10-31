$(document).ready(function() {
	var c_idx = window.sessionStorage.getItem("cmenu_index");
	var t_idx = window.sessionStorage.getItem("nav_id");
	if(!c_idx) {
		c_idx = 0;
	}
	if(!t_idx) {
		t_idx = 0;
	}
	c_idx = parseInt(c_idx);
	t_idx = parseInt(t_idx);
	var titles = getId('top-tit').getElementsByTagName('li');
		if(titles.length <= t_idx) {
			t_idx = 0;
		}
	var asideLis = $('.aside-con div:eq(' + t_idx + ') li');
	if(asideLis.length <= c_idx) {
		c_idx = 0;
	}

	$('.top-tit li:eq(' + t_idx + ')').addClass('select');
	changeRoute($('.aside-con div:eq(' + t_idx + ') li:eq(' + c_idx + ')').data("uri"));

	$(".cmenu").click(function() {
		var uri = $(this).data('uri');
		var index = $(this).index();
		window.sessionStorage.setItem('cmenu_index', index);
		//记录下选择项，刷新时能刷当前页面
		changeRoute(uri);
		return false;
	})
	init(c_idx, t_idx);
})

function changeRoute(src) {
	$('.container').attr('src', src);
}

function getId(id) {
	return typeof id === 'string' ? document.getElementById(id) : id;
}

function init(c_idx, t_idx) {
	//获取鼠标滑过或点击时的标签和要切换内容的元素
	var titles = getId('top-tit').getElementsByTagName('li'),
		divs = getId('aside-con').getElementsByTagName('div');

	//默认显示第一个
	divs[t_idx].style.display = 'block';

	//如果标签的长度与内容的长度不一样，跳出函数
	if(titles.length != divs.length)
		return;

	for(var i = 0; i < titles.length; i++) {

		titles[i].id = i;

		//添加事件
		titles[i].onclick = function() {
			//清除所有li上的class
			for(var j = 0; j < titles.length; j++) {
				titles[j].className = '';
				divs[j].style.display = 'none';
			}
			window.sessionStorage.setItem('cmenu_index', 0);
			window.sessionStorage.setItem('nav_id', this.id);
			//给当前li添加class高亮显示
			titles[this.id].className = 'select';
			divs[this.id].style.display = 'block';
			$('.aside-con div:eq(' + this.id + ') li:eq(0)').addClass('select').siblings("li").removeClass("select");
			//左边标签栏,显示选中的内容
			var li_tabs = $(divs[this.id]).find('li');
			for(var h = 0; h < li_tabs.length; h++) {
				if($(li_tabs[h]).hasClass('select')) {
					changeRoute($(li_tabs[h]).data('uri'));
				}
			}
		}

	}

	// 左侧栏active效果
	$('.aside-con div:eq(' + t_idx + ') li:eq(' + c_idx + ')').addClass('select');
	$('.mod li').on('click', function() {
		$(this).siblings().removeClass('select').end().addClass('select');
		return false;
	})

}