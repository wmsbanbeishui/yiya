<?php

use common\helpers\JsBlock;

?>

    <div id="history" style="border: 1px solid #ccc; width: 100px; height: auto"></div>

    <input type="text" id="content">

    <button onclick="sendMsg()">发送</button>

<?php JsBlock::begin() ?>
    <script>
        var type = 1; // 发送者类型 1-用户；2-客服
        var store_id = 3;
        var user_id = 1;
        var exampleSocket = new WebSocket("ws://47.107.73.157:9502");
        $(function () {
            exampleSocket.onopen = function (event) {
                console.log(event.data);
                initData(); //加载历史记录
            };
            exampleSocket.onmessage = function (event) {
                console.log(event.data);
                loadData($.parseJSON(event.data)); //导入消息记录，加载新的消息
            }
        })

        function sendMsg() {
            var pData = {
                content: document.getElementById('content').value,
                content_type: 1,
                type: type,
                store_id: store_id,
                user_id: user_id,
            }
            if (pData.content == '') {
                alert("消息不能为空");
                return;
            }
            exampleSocket.send(JSON.stringify(pData)); //发送消息
        }

        function initData() {
            var pData = {
                type: type,
                store_id: store_id,
                user_id: user_id,
            }
            exampleSocket.send(JSON.stringify(pData)); //获取消息记录，绑定fd
        }

        function loadData(data) {
            for (var i = 0; i < data.length; i++) {
                if (data[i].type == 1) { // 发送者为用户
                    var add_str = '用户' + data[i].user_id + '发给客服' + data[i].store_id
                } else {
                    add_str = '客服' + data[i].store_id + '发给用户' + data[i].user_id
                }
                var html = '<p>' + add_str + ':' + data[i].content + '</p>';
                $("#history").append(html);
            }
        }

    </script>
<?php JsBlock::end() ?>