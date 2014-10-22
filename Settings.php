<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\TrafficSources;

use Piwik\Settings\SystemSetting;
use Piwik\Settings\UserSetting;
use Piwik\Piwik;

/**
 * Defines Settings for TrafficSources.
 *
 */
class Settings extends \Piwik\Plugin\Settings
{
    /** @var SystemSetting */
    public $refreshInterval;

    /** @var SystemSetting */
    public $currPeriodOfTime;
	
    /** @var SystemSetting */
    public $histPeriodOfTime;
	
    protected function init()
    {
        $this->setIntroduction(Piwik::translate('ConcurrentsByTrafficSource_SettingsIntroduction'));

        // System setting --> textbox converted to int defining a validator and filter
        $this->createRefreshIntervalSetting();

        // System setting --> textbox converted to int defining a validator and filter
        $this->createCurrentPeriodOfTimeSetting();
        
        // System setting --> textbox converted to int defining a validator and filter
        $this->createHistoricalPeriodOfTimeSetting();
        
    }

    private function createRefreshIntervalSetting()
    {
        $this->refreshInterval        = new SystemSetting('refreshInterval', Piwik::translate('ConcurrentsByTrafficSource_SettingsRefreshInterval'));
        $this->refreshInterval->readableByCurrentUser = true;
        $this->refreshInterval->type  = static::TYPE_INT;
        $this->refreshInterval->uiControlType = static::CONTROL_TEXT;
        $this->refreshInterval->uiControlAttributes = array('size' => 3);
        $this->refreshInterval->description     = Piwik::translate('ConcurrentsByTrafficSource_SettingsRefreshIntervalDescription');
        $this->refreshInterval->inlineHelp      = Piwik::translate('ConcurrentsByTrafficSource_SettingsRefreshIntervalHelp');
        $this->refreshInterval->defaultValue    = '30';
        $this->refreshInterval->validate = function ($value, $setting) {
            if ($value < 1) {
                throw new \Exception('Value is invalid');
            }
        };

        $this->addSetting($this->refreshInterval);
    }

    private function createCurrentPeriodOfTimeSetting()
    {
        $this->currPeriodOfTime        = new SystemSetting('currPeriodOfTime', Piwik::translate('ConcurrentsByTrafficSource_SettingsCPOT'));
        $this->currPeriodOfTime->readableByCurrentUser = true;
        $this->currPeriodOfTime->type  = static::TYPE_INT;
        $this->currPeriodOfTime->uiControlType = static::CONTROL_TEXT;
        $this->currPeriodOfTime->uiControlAttributes = array('size' => 3);
        $this->currPeriodOfTime->description     = Piwik::translate('ConcurrentsByTrafficSource_SettingsCPOTDescription');
        $this->currPeriodOfTime->inlineHelp      = Piwik::translate('ConcurrentsByTrafficSource_SettingsCPOTHelp');
        $this->currPeriodOfTime->defaultValue    = '30';
        $this->currPeriodOfTime->validate = function ($value, $setting) {
            if ($value > 30 && $value < 1) {
                throw new \Exception('Value is invalid');
            }
        };

        $this->addSetting($this->currPeriodOfTime);
    }

    private function createHistoricalPeriodOfTimeSetting()
    {
        $this->histPeriodOfTime        = new SystemSetting('histPeriodOfTime', Piwik::translate('ConcurrentsByTrafficSource_SettingsHPOT'));
        $this->histPeriodOfTime->readableByCurrentUser = true;
        $this->histPeriodOfTime->type  = static::TYPE_INT;
        $this->histPeriodOfTime->uiControlType = static::CONTROL_TEXT;
        $this->histPeriodOfTime->uiControlAttributes = array('size' => 3);
        $this->histPeriodOfTime->description     = Piwik::translate('ConcurrentsByTrafficSource_SettingsHPOTDescription');
        $this->histPeriodOfTime->inlineHelp      = Piwik::translate('ConcurrentsByTrafficSource_SettingsHPOTHelp');
        $this->histPeriodOfTime->defaultValue    = '30';
        $this->histPeriodOfTime->validate = function ($value, $setting) {
            if ($value > 30 && $value < 1) {
                throw new \Exception('Value is invalid');
            }
        };

        $this->addSetting($this->histPeriodOfTime);
    }

}
