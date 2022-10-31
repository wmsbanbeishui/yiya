<?php
//var_dump($data);exit;
?>

<style type="text/css">
    .hightlight {
        color: red;
    }
</style>
<?php foreach ($data as $item): ?>
<div class="finance-view" style="padding-bottom: 40px">

        <div>
            标题：<?= $item['highlight']['title'] ? $item['highlight']['title'][0] : $item['_source']['title'] ?>
        </div>
        <div>
            摘要：<?= $item['highlight']['description'] ? $item['highlight']['description'][0] : $item['_source']['description'] ?>
        </div>

</div>
<?php endforeach; ?>