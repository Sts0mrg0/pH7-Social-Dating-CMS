<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2017, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Forum / Form / Processing
 */

namespace PH7;
defined('PH7') or exit('Restricted access');

use PH7\Framework\Mvc\Router\Uri;
use PH7\Framework\Url\Header;

class EditForumFormProcess extends Form
{

    public function __construct()
    {
        parent::__construct();

        $iForumId = $this->httpRequest->get('forum_id', 'int');

        (new ForumModel)->updateForum($iForumId, $this->httpRequest->post('category_id'), $this->httpRequest->post('name'), $this->httpRequest->post('description'), $this->dateTime->get()->dateTime('Y-m-d H:i:s'));
        Header::redirect(Uri::get('forum', 'forum', 'index'), t('Your message has been updated successfully!'));
    }

}
