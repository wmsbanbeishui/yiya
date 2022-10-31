<?php

namespace admin\controllers;

use common\helpers\FileHelper;
use common\helpers\Message;
use common\models\table\Picture;
use admin\models\search\PictureSearch;
use admin\controllers\base\AuthController;
use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * PictureController implements the CRUD actions for Picture model.
 */
class PictureController extends AuthController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Picture models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PictureSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Picture model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Picture model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Picture();

        if ($model->load(Yii::$app->request->post())) {

            $model->name = '酱酱';

            // 上传主图
            if ($_FILES['picture']['name']) {
                $upload_picture = FileHelper::picUpload($_FILES['picture'], $path = 'picture', 1024 * 10240);
                if ($upload_picture['errno'] == 0) {
                    $model->picture = $upload_picture['key'];

                    $file_path = $upload_picture['file_path'];
                    $key = $path . '/' . $upload_picture['file_name'];
                    FileHelper::qnUpload($file_path, $key);
                } else {
                    Message::setErrorMsg($upload_picture['msg']);
                    return $this->render('create', [
                        'model' => $model,
                    ]);
                }
            }

            if ($model->save()) {
                return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Picture model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Picture model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Picture model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Picture the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Picture::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionUpload()
    {
        $request = Yii::$app->getRequest();

        $model = new Picture();

        if ($request->getIsPost()) {
            $model->imageFiles = UploadedFile::getInstances($model, 'picture');
            $model->name = '酱酱';

            $post = $request->post();
            $model->is_push = $post['Picture']['is_push'];
            $model->date = $post['Picture']['date'];

            if ($model->upload()) {
                foreach ($model->up_files as $k => $v) {//添加上传图片记录
                    $_model = clone $model;
                    $_model->picture = $v;
                    $_model->save();
                }
            }
            Message::setSuccessMsg('上传成功');
            return $this->redirect('index');
        }

        return $this->render('upload', [
            'model' => $model
        ]);
    }
}
