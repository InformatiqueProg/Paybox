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

namespace Paybox\Controller;

use Paybox\Paybox;
use Paybox\Model\Config;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Translation\Translator;
use Thelia\Log\Tlog;
use Thelia\Model\ConfigQuery;
use Thelia\Model\OrderQuery;
use Thelia\Model\OrderStatus;
use Thelia\Model\OrderStatusQuery;

/**
 * Class PayboxPayResponse
 * @package Paybox\Controller
 * author Thelia <info@thelia.net>
 */
class PayboxPayResponse extends BaseFrontController
{
    /**
     * @param  int $order_id
     * @return \Thelia\Core\HttpFoundation\Response
     */
    public static function payfail($order_id)
    {
        /*
         * Empty cart
         */
        return $this->render("order-failed", ["failed_order_id" => $order_id]);
    }

    /**
     * @return \Thelia\Core\HttpFoundation\Response
     */
    public function receiveResponse()
    {
        /*
         * Configure log output
         */
        $log = Tlog::getInstance();
        $log->setDestinations("\\Thelia\\Log\\Destination\\TlogDestinationFile");
        $log->setConfig("\\Thelia\\Log\\Destination\\TlogDestinationFile", 0, THELIA_ROOT . "log" . DS . "log-paybox.txt");
        $log->info('accessed');

        $err_msg = '';

        $request = $this->getRequest();
        $order_id = 0;

        /*
         * response signature
         */
        if ($request->query->has('Sign')) {
            $signature = $request->get('Sign');
            // $signature = base64_decode($signature);
            
            /*
             * Check hash value of response
             */
            $vars = explode(';', Paybox::PBX_RETOUR);

            $paramsString = '';

            foreach ($vars as $param) {
                $tempParam = explode(':', $param);

                if ($tempParam[0] != 'Sign' && $request->query->has($tempParam[0])) {
                    $paramsString .= '&' . $tempParam[0] . '=' . $request->get($tempParam[0]);
                }
            }

            $paramsString = ltrim($paramsString, '&');

            $keyFile = __DIR__ . '/../' . Paybox::PBX_KEY_PATH;

            $pubkey = openssl_pkey_get_public(file_get_contents($keyFile));

            if (!openssl_verify($paramsString, $signature, $pubkey)) {
                /**
                 * get Order
                 */
                $order_id = $request->get('Ref');

                if (is_numeric($order_id)) {
                    $order_id = (int) $order_id;
                }

                $order = OrderQuery::create()->findPk($order_id);

                /**
                 * transaction state
                 */
                $codeErr = $request->get('CodeErr');

                if ($codeErr == '00000') {
                    $status = OrderStatusQuery::create()
                        ->findOneByCode(OrderStatus::CODE_PAID);

                    $event = new OrderEvent($order);

                    $event->setStatus($status->getId());

                    $this->dispatch(TheliaEvents::ORDER_UPDATE_STATUS, $event);

                    $log->info('The payment of the order ' . $order->getRef() . ' has been successfully released.');
                } else {
                    $err_msg = 'Transaction error code:' . $codeErr;
                }
            } else {
                $err_msg = 'signature check failed!';
            }
        } else {
            $err_msg = 'signature missing!';
        }

        /**
         * Error ?
         */
        if ($err_msg != '') {
            $log->error($err_msg);

            // $message = \Swift_Message::newInstance(ConfigQuery::read('store_name') . ' - Paybox error')
            //     ->addFrom(ConfigQuery::read('store_email'), ConfigQuery::read('store_name'))
            //     ->addTo(ConfigQuery::read('store_email'), ConfigQuery::read('store_name'))
            //     ->setBody('Paybox Error : ' . $err_msg)
            // ;

            // $this->container->get('mailer')->getSwiftMailer()->send($message);
             
            // $event = new OrderEvent($order);

            // $event->setStatus(OrderStatusQuery::create()->findOneByCode(OrderStatus::CODE_CANCELED)->getId());

            // $this->dispatch(TheliaEvents::ORDER_UPDATE_STATUS,$event);

            return $this->render("order-failed", ["failed_order_id" => $order_id]);
        }

        /*
         * Get log back to previous state
         */
        $log->setDestinations("\\Thelia\\Log\\Destination\\TlogDestinationRotatingFile");

        return Response::create('', 200, array('Content-type' => 'text/plain', 'Pragma' => 'nocache'));
    }
}
