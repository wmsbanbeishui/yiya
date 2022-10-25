<?php

namespace common\widgets\grid;

use kartik\grid\DataColumn as KartikDataColumn;

class DataColumn extends KartikDataColumn
{
	/**
	 * 如果是用 css 控制 td 居中，那么 Select2 的文字也会被居中，所以在这里配置了居中
	 */
    public $hAlign = GridView::ALIGN_CENTER;

    public $vAlign = GridView::ALIGN_MIDDLE;

    public $noWrap = true;
}
