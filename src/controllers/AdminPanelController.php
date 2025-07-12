<?php

namespace CoreDev\Meteo\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;

class AdminPanelController extends Controller
{
    public function actionIndex(): Response
    {
        $plugin = \CoreDev\Meteo\Meteo::getInstance();
        $settings = $plugin->getSettings();
        $request = Craft::$app->getRequest();

        if ($request->getIsPost()) {
            $settingsArray = $request->getBodyParams();
            if ($settingsArray) {
                // Map flat POST fields to selectedVariables array using only granularityOptions keys
                $settingsArray['selectedVariables'] = [];
                foreach (array_keys(\CoreDev\Meteo\models\Settings::$granularityOptions) as $granularity) {
                    $field = $granularity . 'Variables';
                    $settingsArray['selectedVariables'][$granularity] = $settingsArray[$field] ?? [];
                    unset($settingsArray[$field]);
                }
                if (Craft::$app->getPlugins()->savePluginSettings($plugin, $settingsArray)) {
                    Craft::$app->getSession()->setNotice('Weather settings saved.');
                    return $this->redirect('meteo');
                } else {
                    Craft::$app->getSession()->setError('Could not save settings.');
                }
            }
        }

        return $this->renderTemplate('craft-meteo/admin/index', [
            'settings' => $settings,
            'plugin' => $plugin,
        ]);
    }
}
