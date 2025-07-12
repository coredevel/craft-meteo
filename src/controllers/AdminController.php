<?php

namespace CoreDev\Meteo\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;

class AdminController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

    public function actionIndex(): Response
    {
        $plugin = \CoreDev\Meteo\Meteo::getInstance();
        $settings = $plugin->getSettings();
        $request = Craft::$app->getRequest();

        if ($request->getIsPost()) {
            $settingsArray = $request->getBodyParams();
            if ($settingsArray) {
                // Map flat POST fields to selectedVariables and extraVariables arrays using granularity keys
                $settingsArray['selectedVariables'] = [];
                $settingsArray['extraVariables'] = [];
                foreach (array_keys(\CoreDev\Meteo\models\Settings::$granularityOptions) as $granularity) {
                    $field = $granularity . 'Variables';
                    $extraField = 'extra' . ucfirst($granularity) . 'Variables';
                    $settingsArray['selectedVariables'][$granularity] = $settingsArray[$field] ?? [];
                    unset($settingsArray[$field]);
                    $settingsArray['extraVariables'][$granularity] = $settingsArray[$extraField] ?? '';
                    unset($settingsArray[$extraField]);
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

    public function actionClearCache(): Response
    {
        Craft::$app->cache->delete('weather_data');
        Craft::$app->getSession()->setNotice("Weather cache cleared.");
        return $this->redirect('meteo');
    }
}
