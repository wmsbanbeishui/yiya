<?php

namespace common\widgets\grid;

use kartik\grid\GridView as KartikGridView;

/**
 * @see \common\helpers\Render::gridView()
 */
class GridView extends KartikGridView {
	public $dataColumnClass = '\common\widgets\grid\DataColumn';
	public $resizableColumns = false;
	public $bordered = false;
	public $striped = true;
	public $condensed = false;
	public $responsive = true;
	public $responsiveWrap = false;
	public $hover = true;
	public $export = false;
}
