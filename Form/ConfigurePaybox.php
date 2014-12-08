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

namespace Paybox\Form;

use Paybox\Paybox;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Form\BaseForm;
use Thelia\Core\Translation\Translator;

class ConfigurePaybox extends BaseForm
{
    public function getName()
    {
        return "configurepaybox";
    }

    protected function buildForm()
    {
        $values = null;

        $path = __DIR__ . '/../' . Paybox::JSON_CONFIG_PATH;

        if (is_readable($path)) {
            $values = json_decode(file_get_contents($path),true);
        }

        $this->formBuilder
            ->add('PBX_SITE', 'text', array(
                'label' => Translator::getInstance()->trans('SITE'),
                'label_attr' => array(
                    'for' => 'PBX_SITE'
                ),
                'data' => (null === $values ?'':$values["PBX_SITE"]),
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('PBX_RANG', 'text', array(
                'label' => Translator::getInstance()->trans('RANG'),
                'label_attr' => array(
                    'for' => 'PBX_RANG'
                ),
                'data' => (null === $values ?'':$values["PBX_RANG"]),
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('PBX_IDENTIFIANT', 'text', array(
                'label' => Translator::getInstance()->trans('IDENTIFIANT'),
                'label_attr' => array(
                    'for' => 'PBX_IDENTIFIANT'
                ),
                'data' => (null === $values ?'':$values["PBX_IDENTIFIANT"]),
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('PBX_SERVER', 'text', array(
                'label' => Translator::getInstance()->trans('SERVEUR'),
                'label_attr' => array(
                    'for' => 'PBX_SERVER'
                ),
                'data' => (null === $values ?'':$values["PBX_SERVER"]),
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('PBX_PAGE', 'text', array(
                'label' => Translator::getInstance()->trans('PAGE'),
                'label_attr' => array(
                    'for' => 'PBX_PAGE'
                ),
                'data' => (null === $values ?'':$values["PBX_PAGE"]),
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('PBX_KEY', 'text', array(
                'label' => Translator::getInstance()->trans('PRIVATE KEY'),
                'label_attr' => array(
                    'for' => 'PBX_KEY'
                ),
                'data' => (null === $values ?'':$values["PBX_KEY"]),
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('PBX_DEVISE', 'text', array(
                'label' => Translator::getInstance()->trans('DEVISE'),
                'label_attr' => array(
                    'for' => 'PBX_DEVISE'
                ),
                'data' => (null === $values ?'':$values["PBX_DEVISE"]),
                'constraints' => array(
                    new NotBlank()
                )
            ))
        ;
    }
}
