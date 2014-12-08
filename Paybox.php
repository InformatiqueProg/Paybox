<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
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
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace Paybox;

use Paybox\Model\Config;
use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Template\TemplateDefinition;
use Thelia\Model\Base\Template;
use Thelia\Model\ModuleImageQuery;
use Thelia\Model\Order;
use Thelia\Module\AbstractPaymentModule;
use Thelia\Module\BaseModule;
use Thelia\Module\PaymentModuleInterface;
use Thelia\Tools\URL;
use Symfony\Component\Routing\Router;

class Paybox extends AbstractPaymentModule
{
    const JSON_CONFIG_PATH = '/Config/config.json';
    const PBX_KEY_PATH = '/pubkey.pem';
    const PBX_RETOUR = 'Mt:M;Ref:R;Auto:A;CodeErr:E;Sign:K';
    const PBX_URLPAIEMENT = "%s/%s";

    protected $config;

    /**
     * This method is call on Payment loop.
     *
     * If you return true, the payment method will de display
     * If you return false, the payment method will not be display
     *
     * @return boolean
     */
    public function isValidPayment()
    {
        return true;
    }

    /**
     * This method is called just after the module was successfully activated.
     *
     * @param ConnectionInterface $con
     */
    public function postActivation(ConnectionInterface $con = null)
    {
        /* insert the images from image folder if first module activation */
        $module = $this->getModuleModel();

        if (ModuleImageQuery::create()->filterByModule($module)->count() == 0) {
            $this->deployImageFolder($module, sprintf('%s/images', __DIR__), $con);
        }

        /* set module title */
        $this->setTitle(
            $module,
            array(
                'en_US' => 'CB',
                'fr_FR' => 'CB',
            )
        );
    }

    /**
     * @return mixed
     */
    public function pay(Order $order)
    {
        $c = Config::read(Paybox::JSON_CONFIG_PATH);

        $currency = $order->getCurrency()->getCode();

        $opts = '';

        $PayboxRouter = $this->container->get('router.paybox');

        $mainRouter = $this->container->get('router.front');

        $vars = array(
            'url_bank'          => sprintf(self::PBX_URLPAIEMENT, $c['PBX_SERVER'], $c['PBX_PAGE']),
            'date'              => date('c'),
            'montant'           => (string) round($order->getTotalAmount(), 2)*100,
            'reference'         => self::harmonise($order->getId(),'numeric',12),
            'mail'              => $this->getRequest()->getSession()->getCustomerUser()->getEmail(),
            'PBX_SITE'          => $c['PBX_SITE'],
            'PBX_RANG'          => $c['PBX_RANG'],
            'PBX_DEVISE'        => $c['PBX_DEVISE'],
            'PBX_RETOUR'        => self::PBX_RETOUR,
            'PBX_HASH'          => 'SHA512',
            'url_retour'        => URL::getInstance()->absoluteUrl(
                $PayboxRouter->generate('paybox.receive', array(), Router::ABSOLUTE_URL)
            ),
            'url_retour_ok'     => URL::getInstance()->absoluteUrl(
                $mainRouter->generate('order.placed',array('order_id' => (string) $order->getId()), Router::ABSOLUTE_URL)
            ),
            'url_retour_err'    => URL::getInstance()->absoluteUrl(
                $PayboxRouter->generate('paybox.payfail',array('order_id' => (string) $order->getId()), Router::ABSOLUTE_URL)
            ),
            'PBX_IDENTIFIANT'   => $c['PBX_IDENTIFIANT']
        );

        $hashable = 'PBX_SITE=' . $vars['PBX_SITE']
            . '&PBX_RANG=' . $vars['PBX_RANG']
            . '&PBX_IDENTIFIANT=' . $vars['PBX_IDENTIFIANT']
            . '&PBX_TOTAL=' . $vars['montant']
            . '&PBX_DEVISE=' . $vars['PBX_DEVISE']
            . '&PBX_CMD=' . $vars['reference']
            . '&PBX_PORTEUR=' . $vars['mail']
            . '&PBX_RETOUR=' . $vars['PBX_RETOUR']
            . '&PBX_HASH=' . $vars['PBX_HASH']
            . '&PBX_TIME=' . $vars['date']
            . '&PBX_EFFECTUE=' . $vars['url_retour_ok']
            . '&PBX_REPONDRE_A=' . $vars['url_retour']
            . '&PBX_REFUSE=' . $vars['url_retour_err'];

        $mac = self::computeHmac(
            $hashable,
            self::getUsableKey($c['PBX_KEY'])
        );

        $vars['MAC'] = $mac;

        $parser = $this->container->get('thelia.parser');
        $parser->setTemplateDefinition(
            new TemplateDefinition(
                'module_paybox',
                TemplateDefinition::FRONT_OFFICE
            )
        );

        $render = $parser->render('gotobankservice.html',$vars);

        return Response::create($render);
    }

    public static function harmonise($value, $type, $len)
    {
        switch ($type) {
            case 'numeric':
                $value = (string) $value;
                if(mb_strlen($value, 'utf8') > $len);
                $value = substr($value, 0, $len);
                for ($i = mb_strlen($value, 'utf8'); $i < $len; $i++) {
                    $value = '0' . $value;
                }
                break;
            case 'alphanumeric':
                $value = (string) $value;
                if(mb_strlen($value, 'utf8') > $len);
                $value = substr($value, 0, $len);
                for ($i = mb_strlen($value, 'utf8'); $i < $len; $i++) {
                    $value .= ' ';
                }
                break;
        }

        return $value;
    }

    public static function getUsableKey($key)
    {
//        $hexStrKey  = substr($key, 0, 38);
//        $hexFinal   = "" . substr($key, 38, 2) . "00";
//
//        $cca0=ord($hexFinal);
//
//        if ($cca0>70 && $cca0<97)
//            $hexStrKey .= chr($cca0-23) . substr($hexFinal, 1, 1);
//        else {
//            if (substr($hexFinal, 1, 1)=="M")
//                $hexStrKey .= substr($hexFinal, 0, 1) . "0";
//            else
//                $hexStrKey .= substr($hexFinal, 0, 2);
//        }

        return pack("H*", $key);
    }

    public static function computeHmac($sData, $key)
    {
        return strtoupper(hash_hmac("sha512", $sData, $key));
    }

    public static function HtmlEncode($data)
    {
        $SAFE_OUT_CHARS = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890._-";
        $result = "";
        for ($i=0; $i<strlen($data); $i++) {
            if (strchr($SAFE_OUT_CHARS, $data{$i})) {
                $result .= $data{$i};
            } elseif (($var = bin2hex(substr($data,$i,1))) <= "7F") {
                $result .= "&#x" . $var . ";";
            } else
                $result .= $data{$i};

        }

        return $result;
    }

    public function getRequest()
    {
        return $this->container->get('request');
    }

    public function getCode()
    {
        return 'Paybox';
    }
}
