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
use Piwik\Site;
use Piwik\Common;


/**
 * API for plugin ConcurrentsByTrafficSource
 *
 */
class API extends \Piwik\Plugin\API {

	private static function isSocialUrl($url, $socialName = false)
	{
		foreach (Common::getSocialUrls() as $domain => $name) {
	
			if (preg_match('/(^|[\.\/])'.$domain.'([\.\/]|$)/', $url) && ($socialName === false || $name == $socialName)) {
	
				return true;
			}
		}
	
		return false;
	}
	
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
	
	private static function startsWith($haystack, $needle){
    	return $needle === "" || strpos($haystack, $needle) === 0;
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
    public static function getTrafficSources($idSite, $lastMinutes=20)
    {
        \Piwik\Piwik::checkUserHasViewAccess($idSite);
		$timeZoneDiff = API::get_timezone_offset('UTC', Site::getTimezoneFor($idSite));
		$origin_dtz = new \DateTimeZone(Site::getTimezoneFor($idSite));
		$origin_dt = new \DateTime("now", $origin_dtz);
		$refTime = $origin_dt->format('Y-m-d H:i:s');
        $directSql = "SELECT COUNT(*)
                FROM " . \Piwik\Common::prefixTable("log_visit") . "
                WHERE idsite = ?
                AND DATE_SUB('".$refTime."', INTERVAL ? MINUTE) < visit_last_action_time
                AND referer_type = ".Common::REFERRER_TYPE_DIRECT_ENTRY."
                ";
        $direct = \Piwik\Db::fetchOne($directSql, array(
            $idSite, $lastMinutes+($timeZoneDiff/60)
        ));

        $searchSql = "SELECT COUNT(*)
                FROM " . \Piwik\Common::prefixTable("log_visit") . "
                WHERE idsite = ?
                AND DATE_SUB('".$refTime."', INTERVAL ? MINUTE) < visit_last_action_time
                AND referer_type = ".Common::REFERRER_TYPE_SEARCH_ENGINE."
                ";
        $search = \Piwik\Db::fetchOne($searchSql, array(
            $idSite, $lastMinutes+($timeZoneDiff/60)
        ));

        $campaignSql = "SELECT COUNT(*)
                FROM " . \Piwik\Common::prefixTable("log_visit") . "
                WHERE idsite = ?
                AND DATE_SUB('".$refTime."', INTERVAL ? MINUTE) < visit_last_action_time
                AND referer_type = ".Common::REFERRER_TYPE_CAMPAIGN."
                ";
        $campaign = \Piwik\Db::fetchOne($campaignSql, array(
        		$idSite, $lastMinutes+($timeZoneDiff/60)
        ));
        
        $websiteSql = "SELECT COUNT(*)
                FROM " . \Piwik\Common::prefixTable("log_visit") . "
                WHERE idsite = ?
                AND DATE_SUB('".$refTime."', INTERVAL ? MINUTE) < visit_last_action_time
                AND referer_type = ".Common::REFERRER_TYPE_WEBSITE."
                ";
        $website = \Piwik\Db::fetchOne($websiteSql, array(
        		$idSite, $lastMinutes+($timeZoneDiff/60)
        ));

        $socialInternalSql = "SELECT referer_url
                FROM " . \Piwik\Common::prefixTable("log_visit") . "
                WHERE idsite = ?
                AND DATE_SUB('".$refTime."', INTERVAL ? MINUTE) < visit_last_action_time
                AND referer_type = ".Common::REFERRER_TYPE_WEBSITE."
                ";
                
        $socialInternal = \Piwik\Db::fetchAll($socialInternalSql, array(
        		$idSite, $lastMinutes+($timeZoneDiff/60)
        ));
        $socialCount = 0;
        foreach ($socialInternal as &$value) {
        	if(API::isSocialUrl($value['referer_url'])) $socialCount++;
        }
        $internalCount = 0;
        foreach ($socialInternal as &$value) {
        	if(startsWith($value['referer_url'], Site::getMainUrlFor($idSite))) $internalCount++;
        }

        $totalVisits = (int)$direct+$search+$campaign+$website;
        return array(
        	array('name'=>'directVisits', 'value'=>($totalVisits==0)?0:(int)round($direct/$totalVisits*100)),
        	array('name'=>'searchEngineVisits', 'value'=>($totalVisits==0)?0:(int)round($search/$totalVisits*100)),
        	array('name'=>'campaignVisits', 'value'=>($totalVisits==0)?0:(int)round($campaign/$totalVisits*100)),
        	array('name'=>'websiteVisits', 'value'=>($totalVisits==0)?0:(int)round($website/$totalVisits*100)), //subtract socials and internals
        	array('name'=>'socialVisits', 'value'=>($totalVisits==0)?0:(int)round($socialCount/$totalVisits*100)),
        	array('name'=>'internalVisits', 'value'=>($totalVisits==0)?0:(int)round($internalCount/$totalVisits*100))
        );
/*        return array(
            array('name'=>'totalVisits', 'value'=>(int)$direct+$search+$campaign+$website),
        	array('name'=>'directVisits', 'value'=>(int)100),
        	array('name'=>'searchEngineVisits', 'value'=>(int)23),
        	array('name'=>'campaignVisits', 'value'=>(int)2),
        	array('name'=>'websiteVisits', 'value'=>(int)80-(int)32), //subtract socials
        	array('name'=>'socialVisits', 'value'=>(int)32),
        	array('name'=>'internalVisits', 'value'=>(int)22)
        );*/
    }

}
