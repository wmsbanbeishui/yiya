<?php

namespace common\services;

use common\helpers\Helper;
use common\models\table\Picture;
use Yii;
use yii\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;

class IndexService
{
    public static function getIndex($pageSize = 20, $page = 1)
    {
        $query = Picture::find()
            ->where(['is_push' => 1])
            ->asArray();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['date' => SORT_DESC]],
            'pagination' => [
                'pageSize' => $pageSize,
                'page' => $page ? $page - 1 : 0
            ]
        ]);

        $items = $dataProvider->getModels();

        foreach ($items as &$item) {
           // $item['picture'] = Helper::getImageUrl($item['picture']);
            $item['picture'] = 'http://rke29chv6.hn-bkt.clouddn.com/20221031_150309_765.jpg';
        }
        unset($item);

        $pagination = $dataProvider->getPagination();

        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'total' => $dataProvider->getTotalCount(),
                'pageCount' => $pagination->getPageCount(),
                'pageSize' => $pagination->getPageSize(),
                'page' => $pagination->getPage() + 1,
                'items' => $items
            ]
        ];
    }
}