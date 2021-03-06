<?php
/**
 * @title            Design Model Class
 * @desc             Design Model for the HTML contents.
 *
 * @author           Pierre-Henry Soria <hello@ph7cms.com>
 * @copyright        (c) 2012-2017, Pierre-Henry Soria. All Rights Reserved.
 * @license          GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package          PH7 / Framework / Mvc / Model
 */

namespace PH7\Framework\Mvc\Model;

defined('PH7') or exit('Restricted access');

use PH7\Framework\Ads\Ads as Banner;
use PH7\Framework\Cache\Cache;
use PH7\Framework\Layout\Html\Design as HtmlDesign;
use PH7\Framework\Mvc\Model\Engine\Db;
use PH7\Framework\Navigation\Page;
use PH7\Framework\Parse\SysVar;
use PH7\Framework\Registry\Registry;

class Design extends HtmlDesign
{
    const CACHE_STATIC_GROUP = 'db/design/static';
    const CACHE_TIME = 172800;

    /** @var Cache */
    private $oCache;

    public function __construct()
    {
        parent::__construct();
        $this->oCache = new Cache;
    }

    public function langList()
    {
        $sCurrentPage = Page::cleanDynamicUrl('l');
        $oLangs = (new Lang)->getInfos();

        foreach ($oLangs as $sLang) {
            if ($sLang->langId === PH7_LANG_NAME) {
                // Skip the current lang
                continue;
            }

            // Retrieve only the first two characters
            $sAbbrLang = substr($sLang->langId, 0, 2);

            echo '<a href="', $sCurrentPage, $sLang->langId, '" hreflang="', $sAbbrLang, '"><img src="', PH7_URL_STATIC, PH7_IMG, 'flag/s/', $sAbbrLang, '.gif" alt="', t($sAbbrLang), '" title="', t($sAbbrLang), '" /></a>&nbsp;';
        }

        unset($oLangs);
    }

    /**
     * Gets Ads with ORDER BY RAND() SQL aggregate function.
     * With caching, advertising changes every hour.
     *
     * @param integer $iWidth
     * @param integer $iHeight
     * @param boolean $bOnlyActive
     *
     * @return boolean|void
     */
    public function ad($iWidth, $iHeight, $bOnlyActive = true)
    {
        if (!PH7_VALID_LICENSE) {
            return false;
        }

        $this->oCache->start(self::CACHE_STATIC_GROUP, 'ads' . $iWidth . $iHeight . $bOnlyActive, static::CACHE_TIME);

        if (!$oData = $this->oCache->get()) {
            $sSqlActive = ($bOnlyActive) ? ' AND (active=\'1\') ' : ' ';
            $rStmt = Db::getInstance()->prepare('SELECT * FROM ' . Db::prefix('Ads') . 'WHERE (width=:width) AND (height=:height)' . $sSqlActive . 'ORDER BY RAND() LIMIT 1');
            $rStmt->bindValue(':width', $iWidth, \PDO::PARAM_INT);
            $rStmt->bindValue(':height', $iHeight, \PDO::PARAM_INT);
            $rStmt->execute();
            $oData = $rStmt->fetch(\PDO::FETCH_OBJ);
            Db::free($rStmt);
            $this->oCache->put($oData);
        }

        /**
         * Don't display ads on the admin panel.
         */
        if (!(Registry::getInstance()->module === PH7_ADMIN_MOD) && $oData) {
            echo '<div class="inline" onclick="$(\'#ad_' . $oData->adsId . '\').attr(\'src\',\'' . PH7_URL_ROOT . '?' . Banner::PARAM_URL . '=' . $oData->adsId . '\');return true;">';
            echo Banner::output($oData);
            echo '<img src="' . PH7_URL_STATIC . PH7_IMG . 'useful/blank.gif" style="border:0;width:0px;height:0px;" alt="" id="ad_' . $oData->adsId . '" /></div>';
        }
        unset($oData);
    }

    /**
     * Analytics API code.
     *
     * @param boolean $bPrint Print the analytics HTML code.
     * @param boolean $bOnlyActive Only active code.
     *
     * @return string|void
     */
    public function analyticsApi($bPrint = true, $bOnlyActive = true)
    {
        $this->oCache->start(self::CACHE_STATIC_GROUP, 'analyticsApi' . $bOnlyActive, static::CACHE_TIME);

        if (!$sData = $this->oCache->get()) {
            $sSqlWhere = ($bOnlyActive) ? 'WHERE active=\'1\'' : '';
            $rStmt = Db::getInstance()->prepare('SELECT code FROM ' . Db::prefix('AnalyticsApi') . $sSqlWhere . ' LIMIT 1');
            $rStmt->execute();
            $oRow = $rStmt->fetch(\PDO::FETCH_OBJ);
            Db::free($rStmt);
            $sData = $oRow->code;
            unset($oRow);
            $this->oCache->put($sData);
        }

        if (!$bPrint) {
            return $sData;
        }

        echo $sData;
    }

    /**
     * Get the custom code.
     *
     * @param string $sType Choose between 'css' and 'js'.
     *
     * @return string
     */
    public function customCode($sType)
    {
        $this->oCache->start(self::CACHE_STATIC_GROUP, 'customCode' . $sType, static::CACHE_TIME);

        if (!$sData = $this->oCache->get()) {
            $rStmt = Db::getInstance()->prepare('SELECT code FROM ' . Db::prefix('CustomCode') . 'WHERE codeType = :type LIMIT 1');
            $rStmt->bindValue(':type', $sType, \PDO::PARAM_STR);
            $rStmt->execute();
            $oRow = $rStmt->fetch(\PDO::FETCH_OBJ);
            Db::free($rStmt);
            $sData = (!empty($oRow->code)) ? $oRow->code : null;
            unset($oRow);
            $this->oCache->put($sData);
        }

        return $sData;
    }

    /**
     * Get CSS/JS files.
     *
     * @param string $sType Choose between 'css' and 'js'.
     * @param boolean $bOnlyActive If TRUE, it will get only the files activated.
     *
     * @return void HTML output.
     */
    public function files($sType, $bOnlyActive = true)
    {
        $this->oCache->start(self::CACHE_STATIC_GROUP, 'files' . $sType . $bOnlyActive, static::CACHE_TIME);

        if (!$oData = $this->oCache->get()) {
            $sSqlWhere = ($bOnlyActive) ? ' AND active=\'1\'' : '';
            $rStmt = Db::getInstance()->prepare('SELECT file FROM ' . Db::prefix('StaticFiles') . 'WHERE fileType = :type' . $sSqlWhere);
            $rStmt->bindValue(':type', $sType, \PDO::PARAM_STR);
            $rStmt->execute();
            $oData = $rStmt->fetchAll(\PDO::FETCH_OBJ);
            Db::free($rStmt);
            $this->oCache->put($oData);
        }

        if (!empty($oData)) {
            foreach ($oData as $oFile) {
                $sFullPath = (new SysVar)->parse($oFile->file);
                $sMethodName = 'external' . ($sType == 'js' ? 'Js' : 'Css') . 'File';
                $this->$sMethodName($sFullPath);
            }
        }
        unset($oData);
    }
}
