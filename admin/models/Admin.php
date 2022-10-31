<?php

namespace admin\models;

use common\models\table\Admin as AdminTalbe;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class Admin extends AdminTalbe
{
	const STATUS_ENABLE = 1;
	const STATUS_DISABLE = 0;

	public function search($params)
	{
		$query = self::find();
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => [
				'pageSize' => '100',
			],
			'sort' => [
				'defaultOrder' => [
					'id' => SORT_DESC,
				],
			],
		]);

		return $dataProvider;
	}

	public static function map()
	{
		$query = self::find()->select(['id', 'realname'])->where(['status' => 1])->orderBy(["FIELD(`id`,3,2,1,4)"=>true]);
		$data = $query->all();
		$map = ArrayHelper::map($data, 'id', 'realname');
		return $map;
	}

    public static function mineMap()
    {
        $data = self::find()
            ->select(['id', 'realname'])
            ->where(['status' => 1, 'id' => [2, 3]])
            ->orderBy(['id' => SORT_DESC])
            ->asArray()
            ->all();

        $map = ArrayHelper::map($data, 'id', 'realname');
        return $map;
    }
}
