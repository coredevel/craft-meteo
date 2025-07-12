<?php

namespace CoreDev\Meteo\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;

class AdminController extends Controller
{
    protected array|int|bool $allowAnonymous = false;


    public function actionClearCache(): Response
    {
        Craft::$app->cache->delete('weather_data');
        Craft::$app->getSession()->setNotice("Weather cache cleared.");
        return $this->redirect(Craft::$app->getRequest()->getReferrer());
    }
}
