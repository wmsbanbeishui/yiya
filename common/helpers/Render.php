<?php

namespace common\helpers;

use common\widgets\grid\GridView;
use common\widgets\grid\ExportMenu;
use liyunfang\pager\LinkPager;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\data\DataProviderInterface;
use yii\base\InvalidConfigException;

class Render
{
    /**
     * 后台用的表格
     * @param array $config
     * @return string
     */
    public static function gridView($config = [])
    {
        /** @var \yii\data\DataProviderInterface $dataProvider */
        $dataProvider = ArrayHelper::getValue($config, 'dataProvider');
        if (!$dataProvider instanceof DataProviderInterface) {
            throw new InvalidConfigException('The "dataProvider" param must implement DataProviderInterface.');
        }

        $filterModel = ArrayHelper::getValue($config, 'filterModel');
        if (!empty($filterModel) && !($filterModel instanceof Model)) {
            throw new InvalidConfigException('The "filterModel" param must be instance of yii\base\Model');
        }

        $columns = ArrayHelper::getValue($config, 'columns');
        if (!is_array($columns) || empty($columns)) {
            throw new InvalidConfigException('The "columns" param must be a not null array');
        }

        $gridDefaultConfig = [
            'layout' => "{toolbar}{summary}\n{items}\n{pager}",
            'emptyTextOptions' => [
                'class' => ['empty'],
                'style' => ['text-align' => 'center'],
            ],
            'toolbar' => [],
            'pjax' => true,
            'pjaxSettings' => [
                'options' => [
                    'id' => 'kartik-grid-pjax',
                ],
            ],
        ];

        // 设置分页参数
        if ($dataProvider->getPagination()) {
            $gridDefaultConfig['filterSelector'] = 'select[name="' . $dataProvider->getPagination()->pageSizeParam . '"], input[name="' . $dataProvider->getPagination()->pageParam . '"]';
            $gridDefaultConfig['pager'] = [
                'class' => LinkPager::className(),
                'firstPageLabel' => '首页',
                'lastPageLabel' => '尾页',
                'options' => [
                    'class' => ['pagination', 'pagination-sm'],
                    'style' => ['margin-top' => '0', 'margin-bottom' => '-4px'],
                ],
                'pageSizeList' => [10, 15, 20, 30, 50, 100],
                'pageSizeOptions' => [
                    'class' => ['form-control', 'input-sm'],
                    'style' => ['width' => '80px'],
                ],
                'customPageOptions' => [
                    'class' => ['form-control', 'input-sm'],
                    'style' => ['width' => '50px'],
                ],
                'template' => '
				<div class="form-inline">
					<div class="form-group">{pageButtons}</div>
					<div class="form-group">
						<label>跳转到：</label>
						{customPage}
					</div>
					<div class="form-group">
						<label>每页：</label>
						{pageSize}
					</div>
				</div>
				',
                'pageSizeMargin' => null,
                'customPageWidth' => '0',
                'customPageMargin' => null,
            ];
        }

        $gridConfig = ArrayHelper::merge($gridDefaultConfig, $config);

        // 导出列
        $export_columns = ArrayHelper::getValue($config, 'export_columns');
        // 如果没有指定导出列，则为默认列表列
        if (!$export_columns) {
            $export_columns = $columns;
        } else {
            ArrayHelper::remove($gridConfig, 'export_columns');
        }
        $exportConfig = ArrayHelper::remove($gridConfig, 'export');
        if ($exportConfig) {
            $exportDefaultConfig = [
                'dataProvider' => $dataProvider,
                'columns' => $export_columns,
                'exportConfig' => [
                    ExportMenu::FORMAT_HTML => false,
                    ExportMenu::FORMAT_TEXT => false,
                    ExportMenu::FORMAT_PDF => false,
                    // ExportMenu::FORMAT_EXCEL => false,
                ],
                'pjaxContainerId' => 'kartik-grid-pjax',
            ];
            if (is_array($exportConfig) && !empty($exportConfig)) {
                $exportConfig = ArrayHelper::merge($exportDefaultConfig, $exportConfig);
            } else {
                $exportConfig = $exportDefaultConfig;
            }
            $exportMenu = ExportMenu::widget($exportConfig);
            $gridConfig['toolbar'][] = $exportMenu;
        }

        return GridView::widget($gridConfig);
    }
}
