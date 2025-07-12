<?php

namespace CoreDev\Meteo;

use CoreDev\Meteo\controllers\AdminController;
use CoreDev\Meteo\models\Settings;
use CoreDev\Meteo\services\WeatherService;
use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use yii\base\Event;

/**
 * craft-meteo plugin
 *
 * @method static Meteo getInstance()
 * @method Settings getSettings()
 * @author Richard Delph <support@core.je>
 * @copyright Richard Delph
 * @license MIT
 *
 * @property \CoreDev\Meteo\services\WeatherService $weather
 */
class Meteo extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = false;
    public bool $hasCpSection = true;
    public static Meteo $plugin;

    public static function config(): array
    {
        return [
            'components' => [
                'weather' => WeatherService::class,
            ],
        ];
    }

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        // Register Twig variable
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                $event->sender->set('meteo', \CoreDev\Meteo\variables\MeteoVariable::class);
            }
        );
        
        Craft::info(
            Craft::t(
                'craft-meteo',
                'Meteo plugin loaded'
            ),
            __METHOD__
        );

        Craft::$app->onInit(function() {
            // Reserved for post-Craft init logic.
        });

        // Register admin controller for cache clearing
        Craft::$app->controllerMap['craft-meteo/admin'] = AdminController::class;

        // Register CP route for admin panel section (Craft way)
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function($event) {
                $event->rules['meteo'] = 'craft-meteo/admin-panel/index';
            }
        );

        $this->attachEventHandlers();
    }

    public function getCpNavItem(): array
    {
        $nav = parent::getCpNavItem();
        $nav['label'] = 'Meteo Settings';
        $nav['url'] = 'meteo';
        $nav['icon'] = '@CoreDev/Meteo/resources/icon.svg';
        return $nav;
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        // Register AssetBundle for settings page
        Craft::$app->getView()->registerAssetBundle(\CoreDev\Meteo\assetbundles\MeteoCpAsset::class);
        return Craft::$app->view->renderTemplate('craft-meteo/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
    }
}
