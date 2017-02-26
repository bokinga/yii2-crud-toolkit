<?php

namespace voskobovich\crud\actions;

use Yii;
use yii\db\ActiveRecord;
use yii\web\Controller;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * Class UpdateAction.
 */
class UpdateAction extends BaseAction
{
    /**
     * The route which will be redirected after the user action.
     *
     * @var string|array|callable
     */
    public $redirectUrl = ['update', 'id' => ':primaryKey'];

    /**
     * View name.
     *
     * @var string
     */
    public $viewFile = 'update';

    /**
     * Enable or disable ajax validation handler.
     *
     * @var bool
     */
    public $enableAjaxValidation = true;

    /**
     * @throws \yii\web\NotFoundHttpException
     *
     * @return string
     */
    public function run()
    {
        $model = $this->getLoadedModel();

        if (empty($model)) {
            $pk = $this->getPrimaryKey();

            /** @var ActiveRecord $model */
            $model = $this->findModel($pk);
        }
        $model->scenario = $this->scenario;

        $params = Yii::$app->getRequest()->getBodyParams();
        if ($model->load($params)) {
            if ($this->enableAjaxValidation && Yii::$app->request->isAjax && !empty($params['ajax'])) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return ActiveForm::validate($model);
            }

            if ($model->save()) {
                if (is_callable($this->successCallback)) {
                    call_user_func($this->successCallback, $model, $this);
                } elseif ($this->successCallback !== false) {
                    Yii::$app->session->setFlash('update:success');
                }

                if ($this->redirectUrl) {
                    return $this->redirect($model);
                }
            } else {
                if (is_callable($this->errorCallback)) {
                    call_user_func($this->errorCallback, $model, $this);
                } elseif ($this->errorCallback !== false) {
                    Yii::$app->session->setFlash('update:error');
                }
            }
        }

        if (!$this->viewFile) {
            return null;
        }

        /** @var Controller $controller */
        $controller = $this->controller;

        return $controller->render($this->viewFile, [
            'model' => $model,
        ]);
    }
}
