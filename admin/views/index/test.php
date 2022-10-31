<?php

use common\helpers\JsBlock;

?>

<div id="msg"></div>
<input type="text" id="match_id">
<input type="submit" value="发送数据" onclick="song()">


<?php JsBlock::begin() ?>
    <script>
    var msg = document.getElementById("msg");
    var wsServer = 'ws://47.107.73.157:9502';
    //调用websocket对象建立连接：
    //参数：ws/wss(加密)：//ip:port （字符串）
    var websocket = new WebSocket(wsServer);
    //onopen监听连接打开
    websocket.onopen = function (evt) {
        //websocket.readyState 属性：
        /*
        CONNECTING    0    The connection is not yet open.
        OPEN    1    The connection is open and ready to communicate.
        CLOSING    2    The connection is in the process of closing.
        CLOSED    3    The connection is closed or couldn't be opened.
        */
        msg.innerHTML = websocket.readyState;
    };

    function song(){
        var match_id = document.getElementById('match_id').value;
        document.getElementById('match_id').value = '';
        //向服务器发送数据
        websocket.send(match_id);
    }
    //监听连接关闭
    //    websocket.onclose = function (evt) {
    //        console.log("Disconnected");
    //    };

    //onmessage 监听服务器数据推送
    websocket.onmessage = function (evt) {
        msg.innerHTML += evt.data +'<br>';
//        console.log('Retrieved data from server: ' + evt.data);
    };
    //监听连接错误信息
    //    websocket.onerror = function (evt, e) {
    //        console.log('Error occured: ' + evt.data);
    //    };

</script>
<?php JsBlock::end() ?>