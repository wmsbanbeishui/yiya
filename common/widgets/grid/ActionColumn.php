<?php

namespace common\widgets\grid;

use kartik\grid\ActionColumn as KartikActionColumn;

class ActionColumn extends KartikActionColumn {
	public $header = '操作';

	public $noWrap = true;
}
