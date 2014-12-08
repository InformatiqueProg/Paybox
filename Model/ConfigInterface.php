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

namespace Paybox\Model;

interface ConfigInterface
{
    // Data access
    public function write($file=null);
    public static function read($file=null);

    // variables setters
    /*
     * @return Paybox\Model\ConfigInterface
     */
    public function setPayboxSite($PBX_SITE);

    // variables setters
    /*
     * @return Paybox\Model\ConfigInterface
     */
    public function setPayboxRang($PBX_RANG);

    // variables setters
    /*
     * @return Paybox\Model\ConfigInterface
     */
    public function setPayboxIdentifiant($PBX_IDENTIFIANT);

    // variables setters
    /*
     * @return Paybox\Model\ConfigInterface
     */
    public function setPayboxServer($PBX_SERVER);

    // variables setters
    /*
     * @return Paybox\Model\ConfigInterface
     */
    public function setPayboxPage($PBX_PAGE);

    // variables setters
    /*
     * @return Paybox\Model\ConfigInterface
     */
    public function setPayboxKey($PBX_KEY);

    // variables setters
    /*
     * @return Paybox\Model\ConfigInterface
     */
    public function setPayboxDevise($PBX_DEVISE);

}
