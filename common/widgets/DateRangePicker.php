<?php

namespace common\widgets;

use kartik\daterange\DateRangePicker as KartikDateRangePicker;
use kartik\daterange\DateRangePickerAsset;
use kartik\daterange\LanguageAsset;
use kartik\daterange\MomentAsset;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;

/**
 * 这个类是为了减少调用时的配置量，所以写死一些默认的配置，以及减少一些配置的层数
 */
class DateRangePicker extends KartikDateRangePicker
{
    public $convertFormat = true;
    public $presetDropdown = true;
    public $options = [
        'class' => ['form-control'],
    ];
    /**
     * @var bool 根据该属性决定默认配置
     */
    public $dateOnly = true;
    /**
     * @var string 日期格式，如不配置则会根据 $dateOnly 来分配'Y/m/d'或'Y/m/d H:i:s'
     */
    public $dateFormat;
    /**
     * @var string 两个日期之间的分隔符
     */
    public $separator = ' - ';

    /**
     * @inheritdoc
     */
    protected function initSettings()
    {
        $this->pluginOptions = ArrayHelper::merge(
            $this->defaultPluginOptions(),
            $this->pluginOptions
        );
        parent::initSettings();
    }

    protected function defaultPluginOptions()
    {
        $format = $this->dateFormat;
        $pluginOptions = [
            'alwaysShowCalendars' => true,
            'autoApply' => true,
            'showDropdowns' => true,
            'opens' => 'left',
            'locale' => [
                'separator' => $this->separator,
            ],
        ];
        if ($this->dateOnly === false) {
            if ($format == null) {
                $format = 'Y/m/d H:i:s';
            }
            $pluginOptions = ArrayHelper::merge(
                $pluginOptions,
                [
                    'timePicker' => true,
                    'timePicker24Hour' => true,
                    'timePickerIncrement' => 1,
                    'timePickerSeconds' => true,
                    'locale' => [
                        'format' => $format,
                    ],
                ]
            );
        } else {
            if ($format == null) {
                $format = 'Y/m/d';
            }
            $pluginOptions = ArrayHelper::merge(
                $pluginOptions,
                [
                    'locale' => [
                        'format' => $format,
                    ],
                ]
            );
        }
        return $pluginOptions;
    }

    /**
     * @inheritdoc
     */
    protected function initLocale()
    {
        // 重写该方法只是为了在第一行代码处指定资源路径，其他不变
        $this->setLanguage('', Yii::getAlias('@kartik/daterange/assets'));
        if (empty($this->_langFile)) {
            return;
        }
        $localeSettings = ArrayHelper::getValue($this->pluginOptions, 'locale', []);
        $localeSettings += [
            'applyLabel' => Yii::t('kvdrp', 'Apply'),
            'cancelLabel' => Yii::t('kvdrp', 'Cancel'),
            'fromLabel' => Yii::t('kvdrp', 'From'),
            'toLabel' => Yii::t('kvdrp', 'To'),
            'weekLabel' => Yii::t('kvdrp', 'W'),
            'customRangeLabel' => Yii::t('kvdrp', 'Custom Range'),
            'daysOfWeek' => new JsExpression('moment.weekdaysMin()'),
            'monthNames' => new JsExpression('moment.monthsShort()'),
            'firstDay' => new JsExpression('moment.localeData()._week.dow')
        ];
        $this->pluginOptions['locale'] = $localeSettings;
    }

    /**
     * Registers the needed client assets
     */
    public function registerAssets()
    {
        $view = $this->getView();
        MomentAsset::register($view);
        $input = 'jQuery("#' . $this->options['id'] . '")';
        $id = $input;
        if ($this->hideInput) {
            $id = 'jQuery("#' . $this->containerOptions['id'] . '")';
        }
        if (!empty($this->_langFile)) {
            LanguageAsset::register($view)->js[] = $this->_langFile;
        }
        DateRangePickerAsset::register($view);
        $rangeJs = '';
        if (empty($this->callback)) {
            $val = "start.format('{$this->_format}') + '{$this->_separator}' + end.format('{$this->_format}')";
            if (ArrayHelper::getValue($this->pluginOptions, 'singleDatePicker', false)) {
                $val = "start.format('{$this->_format}')";
            }
            $rangeJs = $this->getRangeJs('start') . $this->getRangeJs('end');
            $change = $rangeJs . "{$input}.val(val).trigger('change');";
            if ($this->hideInput) {
                $script = "var val={$val};{$id}.find('.range-value').html(val);{$change}";
            } elseif ($this->useWithAddon) {
                $id = "{$input}.closest('.input-group')";
                $script = "var val={$val};{$change}";
            } elseif (!$this->autoUpdateOnInit) {
                $script = "var val={$val};{$change}";
            } else {
                $this->registerPlugin($this->pluginName, $id);
                return;
            }
            $this->callback = "function(start,end,label){{$script}}";
        }
        // parse input change correctly when range input value is cleared
        $js = <<< JS
{$input}.off('change.kvdrp').on('change.kvdrp', function() {
	var drp = {$id}.data('{$this->pluginName}'), now;
	if ($(this).val() || !drp) {
		return;
	}
	now = moment().format('{$this->_format}') || '';
	drp.setStartDate(now);
	drp.setEndDate(now);
	{$rangeJs}
});
{$input}.focus(function() {
	if (this.value) {
		return;
	}
	now = moment().format('YYYY/MM/DD') || '';
	this.value = now + ' - ' + now;
});
{$input}.dblclick(function() {
	this.value = null;
});
JS;
        $view->registerJs($js);
        $this->registerPlugin($this->pluginName, $id, null, $this->callback);
    }
}
