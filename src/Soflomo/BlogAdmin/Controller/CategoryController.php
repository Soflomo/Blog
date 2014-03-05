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
 * @subpackage  Controller
 * @author      Jurian Sluiman <jurian@soflomo.com>
 * @copyright   2013 Jurian Sluiman.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://soflomo.com
 * @version     @@PACKAGE_VERSION@@
 */

namespace Soflomo\BlogAdmin\Controller;

use Soflomo\Blog\Entity\Category as CategoryEntity;
use Soflomo\Blog\Entity\Blog     as BlogEntity;
use Soflomo\Blog\Exception;
use Soflomo\Blog\Options\ModuleOptions;
use Soflomo\BlogAdmin\Form\Category    as CategoryForm;
use Soflomo\BlogAdmin\Service\Category as CategoryService;
use Zend\Mvc\Controller\AbstractActionController;

class CategoryController extends AbstractActionController
{
    /**
     * @var CategoryService
     */
    protected $service;

    /**
     * @var CategoryForm
     */
    protected $form;

    /**
     * @var ModuleOptions
     */
    protected $options;

    public function __construct(CategoryService $service, CategoryForm $form, ModuleOptions $options = null)
    {
        $this->service = $service;
        $this->form    = $form;

        if (null !== $options) {
            $this->options = $options;
        }
    }

    public function getService()
    {
        return $this->service;
    }

    public function getRepository()
    {
        return $this->getService()->getRepository();
    }

    public function getForm()
    {
        return $this->form;
    }

    public function getOptions()
    {
        if (null === $this->options) {
            $this->options = new ModuleOptions;
        }

        return $this->options;
    }

    public function indexAction()
    {
        $blog       = $this->getBlog();
        $categories = $this->getRepository()->findAll();

        return array(
            'blog'       => $blog,
            'categories' => $categories,
        );
    }

    public function viewAction()
    {
        $blog     = $this->getBlog();
        $category = $this->getCategory();

        $this->addPage(array(
            'label'  => $category->getName(),
            'route'  => 'zfcadmin/blog/category/view',
            'params' => array('blog'   => $blog->getSlug(), 'category' => $category->getId()),
            'active' => true,
        ));

        return array(
            'blog'     => $blog,
            'category' => $category,
        );
    }

    public function createAction()
    {
        $blog     = $this->getBlog();
        $category = $this->getCategory(true);
        $form     = $this->getForm();
        $form->bind($category);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $form->setData($data);

            if ($form->isValid()) {
                $this->getService()->create($category);

                return $this->redirect()->toRoute('zfcadmin/blog/category/view', array(
                    'blog'     => $blog->getSlug(),
                    'category' => $category->getId(),
                ));
            }
        }

        $this->addPage(array(
            'label'  => 'New article',
            'route'  => 'zfcadmin/blog/category/create',
            'params' => array('blog'   => $blog->getSlug()),
            'active' => true,
        ));

        return array(
            'blog'    => $blog,
            'form'    => $form,
        );
    }

    public function updateAction()
    {
        $blog     = $this->getBlog();
        $category = $this->getCategory();
        $form     = $this->getForm();
        $form->bind($category);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $form->setData($data);

            if ($form->isValid()) {
                $this->getService()->update($category);

                return $this->redirect()->toRoute('zfcadmin/blog/category/update', array(
                    'blog'     => $blog->getSlug(),
                    'category' => $category->getId(),
                ));
            }
        }

        $this->addPage(array(
            'label'  => $category->getName(),
            'route'  => 'zfcadmin/blog/category/view',
            'params' => array('blog'   => $blog->getSlug(), 'category' => $category->getId()),
            'active' => true,
            'pages' => array(
                array(
                    'label'  => 'Update category',
                    'route'  => 'zfcadmin/blog/category/update',
                    'params' => array('blog'   => $blog->getSlug(), 'category' => $category->getId()),
                    'active' => true,
                ),
            ),
        ));

        return array(
            'blog'     => $blog,
            'category' => $category,
            'form'     => $form,
        );
    }

    public function deleteAction()
    {
        $blog     = $this->getBlog();
        $category = $this->getCategory();
        $service  = $this->getService();

        $service->delete($category);

        return $this->redirect()->toRoute('zfcadmin/blog', array(
            'blog' => $blog->getSlug(),
        ));
    }

    protected function getBlog()
    {
        $slug = $this->params('blog');
        $repo = $this->getService()->getBlogRepository();
        $blog = $repo->findOneBySlug($slug);

        if (null === $blog) {
            throw new Exception\BlogNotFoundException(sprintf(
                'Blog with slug "%s" not found', $slug
            ));
        }

        return $blog;
    }

    protected function getCategory($create = false)
    {
        if (true === $create) {
            $class   = $this->getOptions()->getCategoryEntityClass();
            $category = new $class;

            return $category;
        }

        $id       = $this->params('category');
        $category = $this->getRepository()->find($id);

        if (null === $category) {
            throw new Exception\CategoryNotFoundException(sprintf(
                'Category with id "%s" not found', $id
            ));
        }

        return $category;
    }

    protected function addPage(array $config = array())
    {
        $admin = $this->getServiceLocator()->get('admin_navigation');
        $found = false;

        // We need to query the page ourselves as
        // $admin->findOneByRoute('zfcadmin/blog')
        // does not load the page by reference

        foreach ($admin->getPages() as $page) {
            if ($page->getRoute() === 'zfcadmin/blog') {
                $found = true;
                break;
            }
        }

        if (!$found) {
            return;
        }

        $page->addPage($config);
    }
}