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

use Paybox\Paybox;
use Thelia\Core\Translation\Translator;

class Config implements ConfigInterface
{
    protected $PBX_SITE = null;
    protected $PBX_RANG = null;
    protected $PBX_IDENTIFIANT = null;
    protected $PBX_SERVER = null;
    protected $PBX_PAGE = null;
    protected $PBX_KEY = null;
    protected $PBX_DEVISE = null;

    public function __construct()
    {
        $config = null;

        try {
            $config=$this->read();
        } catch (\Exception $e) {}

        if ($config !== null) {
            foreach ($config as $key=>$val) {
                try {
                    $this->__set($key,$val);
                } catch (\Exception $e) {}
            }
        }
    }

    public function write($file=null)
    {
        $path = __DIR__ . '/../' . $file;

        if ((file_exists($path) ? is_writable($path):is_writable(__DIR__ . '/../Config/'))) {
            $vars= get_object_vars($this);

            $cond = true;

            foreach($vars as $key => $var)
                $cond &= !empty($var);

            if ($cond) {
                $file = fopen($path, 'w');

                fwrite($file, json_encode($vars));

                fclose($file);
            }
        } else {
            throw new \Exception(Translator::getInstance()->trans("Can't write file", [], 'paybox') . $file . ". "
                . Translator::getInstance()->trans("Please change the rights on the file and/or directory.", [], 'paybox'));
        }
    }

    /**
     * @return array
     */
    public static function read($file=null)
    {
        $path = __DIR__ . '/../' . $file;

        $ret = null;

        if (is_readable($path)) {
            $json = json_decode(file_get_contents($path), true);

            if ($json !== null) {
                $ret = $json;
            } else {
                throw new \Exception(Translator::getInstance()->trans("Can't read file", [], 'paybox') . $file . ". "
                    . Translator::getInstance()->trans("The file is corrupted.", [], 'paybox'));
            }
        } elseif (!file_exists($path)) {
            throw new \Exception(Translator::getInstance()->trans("The file %title doesn't exist.", ['%title' => $file], 'paybox')
                . Translator::getInstance()->trans("You have to create it in order to use this module. Please see module's configuration page.", [], 'paybox'));
        } else {
            throw new \Exception(Translator::getInstance()->trans("Can't read file", [], 'paybox') . $file . ". "
                . Translator::getInstance()->trans("Please change the rights on the file.", [], 'paybox'));
        }

        return $ret;
    }

    /**
     * @param  string $PBX_PAGE
     * @return $this
     */
    public function setPayboxSite($PBX_SITE)
    {
        $this->PBX_SITE = $PBX_SITE;

        return $this;
    }

    /**
     * @param  string $PBX_PAGE
     * @return $this
     */
    public function setPayboxRang($PBX_RANG)
    {
        $this->PBX_RANG = $PBX_RANG;

        return $this;
    }

    /**
     * @param  string $PBX_PAGE
     * @return $this
     */
    public function setPayboxIdentifiant($PBX_IDENTIFIANT)
    {
        $this->PBX_IDENTIFIANT = $PBX_IDENTIFIANT;

        return $this;
    }

    /**
     * @param  string $PBX_PAGE
     * @return $this
     */
    public function setPayboxServer($PBX_SERVER)
    {
        $this->PBX_SERVER = $PBX_SERVER;

        return $this;
    }

    /**
     * @param  string $PBX_PAGE
     * @return $this
     */
    public function setPayboxPage($PBX_PAGE)
    {
        $this->PBX_PAGE = $PBX_PAGE;

        return $this;
    }

    /**
     * @param  string $PBX_PAGE
     * @return $this
     */
    public function setPayboxDevise($PBX_DEVISE)
    {
        $this->PBX_DEVISE = $PBX_DEVISE;

        return $this;
    }

    /**
     * @param  string $PBX_PAGE
     * @return $this
     */
    public function setPayboxKey($PBX_KEY)
    {
        $this->PBX_KEY = $PBX_KEY;

        return $this;
    }

}
