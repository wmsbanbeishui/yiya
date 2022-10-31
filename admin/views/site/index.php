<?php

/**
 * @var $this yii\web\View
 */

use admin\models\AdminMenu;
use common\helpers\Helper;
use yii\helpers\Html;

$user = Yii::$app->getUser()->getIdentity();

$avatar = '/static/admin/images/test_icon.png';

$this->title = 'yiya';
$admin_logo = '';
if (strpos($_SERVER['HTTP_HOST'], '.local.')) {
    $this->title .= '【本地版】';
    $admin_logo = '';
}
if (strpos($_SERVER['HTTP_HOST'], '.dev.')) {
    $this->title .= '【开发版】';
    $admin_logo = '';
}
if (strpos($_SERVER['HTTP_HOST'], '.test.')) {
    $this->title .= '【测试版】';
    $admin_logo = '';
}

?>
<!DOCTYPE html>
<html lang="cn" use-rem="1920">

<head>
    <meta charset="UTF-8">
    <title><?= $this->title ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
    <meta name="renderer" content="webkit">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <meta content="telephone=no" name="format-detection"/>
    <link rel="stylesheet" type="text/css" href="/static/admin/css/lib/reset.css"/>
    <link rel="stylesheet" type="text/css" href="/static/admin/css/index.css"/>
    <script src="/static/admin/js/jquery/2.2.4/jquery.js" type="text/javascript" charset="utf-8"></script>
    <script src="/static/admin/js/jquery/jquery.json.js" type="text/javascript" charset="utf-8"></script>
</head>

<body>

<!--顶部导航，可以封装成公共部分-->
<div class="top-nav">
    <!--logo-->
    <!--<a class="top-logo" href="/">
		<img src="<? /*=$admin_logo*/ ?>"/>
	</a>-->
    <div id="top-tit" class="top-tit">
        <ul>
            <li>
                <a style="text-decoration: none">管理</a>
            </li>
        </ul>
    </div>
    <div class="top-right">
        <div class="search-parent"></div>
        <div class="name-address">
            <?= $user->name ?>
        </div>
        <div class="member-header">
            <img src="<?= $avatar ?>"/>
        </div>
        <div class="close-img">
            <?= Html::beginForm(['/user/logout'], 'post', ['onsubmit' => 'return resub()']) ?>
            <input type="image" src="/static/admin/images/close-site.png" name="submit" align="">
            <?= Html::endForm() ?>
        </div>

    </div>
</div>

<!--左侧内容区域-->
<section id="aside-con" class="aside-con">
    <div class="mod">
        <ul class="aside-con-left-ul">
            <li class="cmenu" data-uri="/index/index">
                <a href="/index/index" style="text-decoration: none">
                    <i class="i-bg"></i>
                    <i class="i-bg-select"></i>
                    <span>首页</span>
                </a>
            </li>
            <li class="cmenu" data-uri="/picture/index">
                <a href="/picture/index" style="text-decoration: none">
                    <i class="i-bg"></i>
                    <i class="i-bg-select"></i>
                    <span>列表</span>
                </a>
            </li>
            <!--<li class="cmenu" data-uri="/upload/upload">
                <a href="/upload/upload" style="text-decoration: none">
                    <i class="i-bg"></i>
                    <i class="i-bg-select"></i>
                    <span>上传</span>
                </a>
            </li>-->
        </ul>
    </div>
</section>
<iframe name="workSpace" src="/index/index" class="container" id="myiframe" scrolling="no"
        onload="changeFrameHeight()"></iframe>
<div class="bottom-record"><a target="_blank" href="https://beian.miit.gov.cn">备案号:粤ICP备2020133048号</a></div>
</body>
<script src="/static/admin/js/index_1.js" type="text/javascript" charset="utf-8"></script>
</html>
<script type="text/javascript">
    function resub() {
        return confirm('确认退出?');
    }

    function changeFrameHeight() {
        var ifm = document.getElementById("myiframe");
        ifm.height = document.documentElement.clientHeight;

    }

    window.onresize = function () {
        changeFrameHeight();

    }
</script>
