<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\TrafficSources;

use Piwik\API\Request;
use \DateTimeZone;
use Piwik\Settings\SystemSetting;
use Piwik\Settings\UserSetting;
use Piwik\Settings\Manager as SettingsManager;
use Piwik\Site;
use Piwik\Common;


/**
 * API for plugin ConcurrentsByTrafficSource
 *
 */
class API extends \Piwik\Plugin\API {

	private static function get_timezone_offset($remote_tz, $origin_tz = null) {
    		if($origin_tz === null) {
        		if(!is_string($origin_tz = date_default_timezone_get())) {
            			return false; // A UTC timestamp was returned -- bail out!
        		}
    		}
    		$origin_dtz = new \DateTimeZone($origin_tz);
    		$remote_dtz = new \DateTimeZone($remote_tz);
    		$origin_dt = new \DateTime("now", $origin_dtz);
    		$remote_dt = new \DateTime("now", $remote_dtz);
    		$offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
    		return $offset;
	}
    /**
     * Retrieves visit count from lastMinutes and peak visit count from lastDays
     * in lastMinutes interval for site with idSite.
     *
     * @param int $idSite
     * @param int $lastMinutes
     * @param int $lastDays
     * @return int
     */
    public static function getVisitorCounter($idSite, $lastMinutes=20)
    {
        \Piwik\Piwik::checkUserHasViewAccess($idSite);
		$timeZoneDiff = API::get_timezone_offset('UTC', Site::getTimezoneFor($idSite));

        $totalSql = "SELECT COUNT(*)
                FROM " . \Piwik\Common::prefixTable("log_visit") . "
                WHERE idsite = ?
                AND DATE_SUB(NOW(), INTERVAL ? MINUTE) < visit_last_action_time
                ";
        $total = \Piwik\Db::fetchOne($totalSql, array(
        		$idSite, $lastMinutes+($timeZoneDiff/60)
        ));
        
        $directSql = "SELECT COUNT(*)
                FROM " . \Piwik\Common::prefixTable("log_visit") . "
                WHERE idsite = ?
                AND DATE_SUB(NOW(), INTERVAL ? MINUTE) < visit_last_action_time
                AND referer_type = ".Common::REFERRER_TYPE_DIRECT_ENTRY."
                ";
        $direct = \Piwik\Db::fetchOne($directSql, array(
            $idSite, $lastMinutes+($timeZoneDiff/60)
        ));

        $searchSql = "SELECT COUNT(*)
                FROM " . \Piwik\Common::prefixTable("log_visit") . "
                WHERE idsite = ?
                AND DATE_SUB(NOW(), INTERVAL ? MINUTE) < visit_last_action_time
                AND referer_type = ".Common::REFERRER_TYPE_SEARCH_ENGINE."
                ";
        $search = \Piwik\Db::fetchOne($searchSql, array(
            $idSite, $lastMinutes+($timeZoneDiff/60)
        ));

        $campaignSql = "SELECT COUNT(*)
                FROM " . \Piwik\Common::prefixTable("log_visit") . "
                WHERE idsite = ?
                AND DATE_SUB(NOW(), INTERVAL ? MINUTE) < visit_last_action_time
                AND referer_type = ".Common::REFERRER_TYPE_CAMPAIGN."
                ";
        $campaign = \Piwik\Db::fetchOne($campaignSql, array(
        		$idSite, $lastMinutes+($timeZoneDiff/60)
        ));
        
        $websiteSql = "SELECT COUNT(*)
                FROM " . \Piwik\Common::prefixTable("log_visit") . "
                WHERE idsite = ?
                AND DATE_SUB(NOW(), INTERVAL ? MINUTE) < visit_last_action_time
                AND referer_type = ".Common::REFERRER_TYPE_WEBSITE."
                ";
        $website = \Piwik\Db::fetchOne($websiteSql, array(
        		$idSite, $lastMinutes+($timeZoneDiff/60)
        ));

        $socialSql = "SELECT referer_url
                FROM " . \Piwik\Common::prefixTable("log_visit") . "
                WHERE idsite = ?
                AND DATE_SUB(NOW(), INTERVAL ? MINUTE) < visit_last_action_time
                AND referer_type = ".Common::REFERRER_TYPE_WEBSITE."
                ";
                
        $social = \Piwik\Db::fetchAll($socialSql, array(
        		$idSite, $lastMinutes+($timeZoneDiff/60)
        ));
        $socialCount = 0;
        foreach ($social as &$value) {
        	if(isSocialUrl($value['referer_url'])) $socialCount++;
        }
        
        return array(
            'totalVisits' => (int)$total,
        	'directVisits' => (int)$direct,
        	'searchEngineVisits' => (int)$search,
        	'campaignVisits' => (int)$campaign,
        	'websiteVisits' => (int)$website,
        	'socialVisits' => (int)$socialCount
        );
    }

}
