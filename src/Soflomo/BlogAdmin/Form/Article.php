<?php
/**
 * Copyright (c) 2013 Jurian Sluiman.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of the
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package     Soflomo\BlogAdmin
 * @subpackage  Form
 * @author      Jurian Sluiman <jurian@soflomo.com>
 * @copyright   2013 Jurian Sluiman.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://soflomo.com
 * @version     @@PACKAGE_VERSION@@
 */

namespace Soflomo\BlogAdmin\Form;

use Doctrine\Common\Persistence\ObjectRepository as CategoryRepository;

use Zend\InputFilter;
use Zend\Form\Form;

class Article extends Form implements
    InputFilter\InputFilterProviderInterface
{
    protected $repository;

    protected $categories;

    public function __construct($name = null, CategoryRepository $repository)
    {
        parent::__construct($name);
        $this->repository = $repository;

        $this->add(array(
            'name'    => 'title',
            'options' => array(
                'label' => 'Title'
            ),
        ));

        $this->add(array(
            'name'    => 'lead',
            'options' => array(
                'label' => 'Lead'
            ),
            'attributes' => array(
                'type'  => 'textarea',
            ),
        ));

        $this->add(array(
            'name'    => 'body',
            'options' => array(
                'label' => 'Body'
            ),
            'attributes' => array(
                'type'  => 'textarea',
            ),
        ));

        $this->add(array(
            'name'    => 'publish_date',
            'type'    => 'datetime',
            'options' => array(
                'format'  => 'Y-m-d H:i',
                'label' => 'Publish date'
            ),
        ));

        $categories = $this->getCategories();
        $list       = array(0 => '');
        foreach ($categories as $category) {
            $list[$category->getId()] = $category->getName();
        }

        $this->add(array(
            'name'    => 'category',
            'type'    => 'select',
            'options' => array(
                'label' => 'Category',
            ),
            'attributes' => array(
                'options' => $list,
            ),
        ));
    }

    public function getInputFilterSpecification()
    {
        $categories = $this->getCategories();
        $list       = array();
        foreach ($categories as $category) {
            $list[] = $category->getId();
        }

        return array(
            'title' => array(
                'required' => true,
                'filters'  => array(
                    array('name' => 'stringtrim'),
                ),
            ),
            'lead'  => array(
                'required' => false,
                'filters'  => array(
                    array('name' => 'stringtrim'),
                    array('name' => 'htmlpurifier'),
                ),
            ),
            'body'  => array(
                'required' => false,
                'filters'  => array(
                    array('name' => 'stringtrim'),
                    array('name' => 'htmlpurifier'),
                ),
            ),
            'publish_date' => array(
                'required' => false,
            ),
            'categories' => array(
                'required'  => false,
                'validator' => array(
                    array('name' => 'in_array', 'options' => array('hay_stack' => $list)),
                )
            ),
        );
    }

    protected function getCategories()
    {
        if (null !== $this->categories) {
            return $this->categories;
        }

        $this->categories = $this->repository->findAll();
        return $this->categories;
    }
}