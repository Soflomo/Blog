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

use Soflomo\Blog\Entity\Article as ArticleEntity;
use Soflomo\Blog\Entity\Blog    as BlogEntity;
use Soflomo\Blog\Exception;
use Soflomo\Blog\Options\ModuleOptions;
use Soflomo\BlogAdmin\Form\Article    as ArticleForm;
use Soflomo\BlogAdmin\Service\Article as ArticleService;
use Zend\Mvc\Controller\AbstractActionController;

class ArticleController extends AbstractActionController
{
    /**
     * @var ArticleService
     */
    protected $service;

    /**
     * @var ArticleForm
     */
    protected $form;

    /**
     * @var ModuleOptions
     */
    protected $options;

    public function __construct(ArticleService $service, ArticleForm $form, ModuleOptions $options = null)
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
        $blog        = $this->getBlog();
        $page        = $this->params('page');
        $limit       = $this->getOptions()->getAdminListingLimit();
        $paginator   = $this->getRepository()->findListing($blog, $page, $limit, true);
        $unpublished = $this->getRepository()->findUnpublished($blog);

        return array(
            'blog'        => $blog,
            'paginator'   => $paginator,
            'unpublished' => $unpublished,
        );
    }

    public function viewAction()
    {
        $blog    = $this->getBlog();
        $article = $this->getArticle($blog);

        $this->addPage(array(
            'label'  => $article->getTitle(),
            'route'  => 'zfcadmin/blog/article/view',
            'params' => array('blog'   => $blog->getSlug(), 'article' => $article->getId()),
            'active' => true,
        ));

        return array(
            'blog'    => $blog,
            'article' => $article,
        );
    }

    public function createAction()
    {
        $blog    = $this->getBlog();
        $article = $this->getArticle($blog, true);
        $form    = $this->getForm();
        $form->bind($article);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $form->setData($data);

            if ($form->isValid()) {
                $this->getService()->create($article);

                $this->flashMessenger()->addMessage('Article created successfully.');
                return $this->redirect()->toRoute('zfcadmin/blog/article/view', array(
                    'blog'    => $blog->getSlug(),
                    'article' => $article->getId(),
                ));
            }
        }

        $this->addPage(array(
            'label'  => 'New article',
            'route'  => 'zfcadmin/blog/article/create',
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
        $blog    = $this->getBlog();
        $article = $this->getArticle($blog);
        $form    = $this->getForm();
        $form->bind($article);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $form->setData($data);

            if ($form->isValid()) {
                $this->getService()->update($article);

                $this->flashMessenger()->addMessage('Article saved successfully.');
                return $this->redirect()->toRoute('zfcadmin/blog/article/update', array(
                    'blog'    => $blog->getSlug(),
                    'article' => $article->getId(),
                ));
            }
        }

        $this->addPage(array(
            'label'  => $article->getTitle(),
            'route'  => 'zfcadmin/blog/article/view',
            'params' => array('blog'   => $blog->getSlug(), 'article' => $article->getId()),
            'active' => true,
            'pages' => array(
                array(
                    'label'  => 'Update article',
                    'route'  => 'zfcadmin/blog/article/update',
                    'params' => array('blog'   => $blog->getSlug(), 'article' => $article->getId()),
                    'active' => true,
                ),
            ),
        ));

        return array(
            'blog'    => $blog,
            'article' => $article,
            'form'    => $form,
        );
    }

    public function deleteAction()
    {
        $blog    = $this->getBlog();
        $article = $this->getArticle($blog);
        $service = $this->getService();

        $service->delete($article);

        $this->flashMessenger()->addMessage('Article deleted successfully.');
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

    protected function getArticle(BlogEntity $blog, $create = false)
    {
        if (true === $create) {
            $class   = $this->getOptions()->getArticleEntityClass();
            $article = new $class;
            $article->setBlog($blog);

            return $article;
        }

        $id      = $this->params('article');
        $article = $this->getRepository()->find($id);

        if (null === $article) {
            throw new Exception\ArticleNotFoundException(sprintf(
                'Article with id "%s" not found', $id
            ));
        } elseif ($article->getBlog()->getId() !== $blog->getId()) {
            throw new Exception\ArticleNotFoundException(sprintf(
                'Article with id "%s" is not part of blog %s', $id, $blog->getSlug()
            ));
        }

        return $article;
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