<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia                                                                       */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*      along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace Paybox\Controller;

use Paybox\Paybox;
use Paybox\Model\Config;
use Paybox\Form\ConfigurePaybox;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Translation\Translator;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Security\AccessManager;

class PayboxSaveConfig extends BaseAdminController
{
    public function save()
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('Paybox'), AccessManager::UPDATE)) {
            return $response;
        }

        $error_message = '';

        $conf = new Config();

        $form = new ConfigurePaybox($this->getRequest());

        try {
            $vform = $this->validateForm($form);
            
            $site = $vform->get('PBX_SITE')->getData();
            $rang = $vform->get('PBX_RANG')->getData();
            $identifiant = $vform->get('PBX_IDENTIFIANT')->getData();
            $serv = $vform->get('PBX_SERVER')->getData();
            $page = $vform->get('PBX_PAGE')->getData();
            $devise = $vform->get('PBX_DEVISE')->getData();
            $key = $vform->get('PBX_KEY')->getData();

            $conf->setPayboxSite($site)
                ->setPayboxRang($rang)
                ->setPayboxIdentifiant($identifiant)
                ->setPayboxServer($serv)
                ->setPayboxPage($page)
                ->setPayboxDevise($devise)
                ->setPayboxKey($key)
                ->write(Paybox::JSON_CONFIG_PATH)
            ;
        } catch (\Exception $e) {
            $error_message = $e->getMessage();
        }

        $this->setupFormErrorContext(
            Translator::getInstance()->trans('Error in form syntax, please check that your values are correct.'),
            $error_message,
            $form
        );

        $this->redirectToRoute('admin.module.configure', array(),
            array ('module_code'=>"Paybox",
                '_controller' => 'Thelia\\Controller\\Admin\\ModuleController::configureAction'));
    }
}
