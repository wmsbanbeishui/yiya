<?php

namespace common\extensions;

use Yii;

trait QueryTrait
{
    /**
     * @var string 时间分隔符
     */
    public $timeRangeSeparator = '-';

    /**
     * @param string $attribute
     *
     * @param string $expression 格式可以是 "1,<2,!=3" 或 "1 <2 !=3"
     * @return $this
     * @see Helper::unifyLimiter() 你看这个方法就明白了
     *
     */
    public function compare($attribute, $expression)
    {
        $value = '';
        $expression = "$expression";
        $conditions = explode(',', \common\helpers\Helper::unifyLimiter($expression));
        foreach ($conditions as $condition) {
            if (preg_match('/^(?:\s*(<>|!=|<=|>=|<|>|=))?(.*)$/', $condition, $matches)) {
                $op = $matches[1];
                $value = $matches[2];
            } else {
                $op = '';
            }

            if ($value === '') {
                continue;
                // return $this;
            }

            if ($op === '') {
                $op = '=';
            }

            $this->andFilterWhere([$op, $attribute, $value]);
        }

        return $this;
    }

    /**
     * @param string $attribute
     * @param string $value
     * @param bool $dateOnly 输入的格式是否仅为日期（不包含时间）
     * @param bool $formatDate 日期格式化成时间戳
     * @return $this
     * @see \common\widgets\DateRangePicker
     *
     */
    public function timeRangeFilter($attribute, $value, $dateOnly = true, $formatDate = false)
    {
        if (!$value) {
            return $this;
        }
        $conditions = explode($this->timeRangeSeparator, $value);
        if (count($conditions) != 2) {
            return $this;
        }

        $from = trim($conditions[0]);
        $to = trim($conditions[1]);

        if ($dateOnly) {
            $from = $formatDate ? strtotime($from) : "{$from} 00:00:00";
            $to = $formatDate ? strtotime($to) + 86399 : "{$to} 23:59:59";
        } else {
            $from = $formatDate ? strtotime($from) : $from;
            $to = $formatDate ? strtotime($to) : $to;
        }

        return $this->andFilterWhere(['between', $attribute, $from, $to]);
    }

    /**
     * @param $command \yii\db\Command
     * @return mixed
     */
    protected function setCommandCache($command)
    {
        $noCache = 0;
        if (!isset($_SERVER['argv']) && isset($_SERVER['HTTP_HOST'])) {
            $noCache = Yii::$app->getRequest()->get('nmc_no_cache', 0);
            if (!in_array($noCache, [0, 1])) {
                $noCache = 0;
            }
        }
        if ($noCache == 1) {
            $command->cache(-1);
        } elseif ($this->queryCacheDuration !== null || $this->queryCacheDependency !== null) {
            $duration = $this->queryCacheDuration === true ? null : $this->queryCacheDuration;
            $command->cache($duration, $this->queryCacheDependency);
        }
        return $command;
    }
}
