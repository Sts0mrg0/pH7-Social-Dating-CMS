<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2014-2017, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Chatroulette / Config
 */

namespace PH7;

defined('PH7') or die('Restricted access');

class Permission extends PermissionCore
{
    public function __construct()
    {
        parent::__construct();

        // If the admin is not logged (but can be if the admin use "login as user" feature)
        if (!AdminCore::auth() || UserCore::isAdminLoggedAs()) {
            if (!$this->checkMembership() || !$this->group->chatroulette) {
                $this->paymentRedirect();
            }
        }
    }
}
