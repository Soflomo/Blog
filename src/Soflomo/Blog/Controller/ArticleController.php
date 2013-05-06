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
 * @package     Soflomo\Blog
 * @subpackage  Controller
 * @author      Jurian Sluiman <jurian@soflomo.com>
 * @copyright   2013 Jurian Sluiman.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://soflomo.com
 * @version     @@PACKAGE_VERSION@@
 */

namespace Soflomo\Blog\Controller;

use DateTime;
use BaconStringUtils\Slugifier;

use Soflomo\Blog\Exception;
use Soflomo\Blog\Options\ModuleOptions;
use Soflomo\Blog\Repository\Article as ArticleRepository;
use Doctrine\ORM\EntityRepository   as BlogRepository;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\FeedModel;

class ArticleController extends AbstractActionController
{
    const DEFAULT_FEED_DESCRIPTION = 'Blog feed of %s';

    /**
     * @var ArticleRepository
     */
    protected $articleRepository;

    /**
     * @var  BlogRepository
     */
    protected $blogRepository;

    /**
     * @var ModuleOptions
     */
    protected $options;

    public function __construct(BlogRepository $blogRepository, ArticleRepository $articleRepository, ModuleOptions $options = null)
    {
        $this->blogRepository    = $blogRepository;
        $this->articleRepository = $articleRepository;

        if (null !== $options) {
            $this->options = $options;
        }
    }

    public function getBlogRepository()
    {
        return $this->blogRepository;
    }

    public function getArticleRepository()
    {
        return $this->articleRepository;
    }

    public function getOptions()
    {
        if (null === $this->options) {
            $this->options = new ModuleOptions;
        }

        return $this->options;
    }

    public function recentAction()
    {
        $blog     = $this->getBlog();
        $limit    = $this->getOptions()->getRecentListingLimit();
        $articles = $this->getArticleRepository()->findRecent($blog, $limit);

        return array(
            'articles' => $articles,
        );
    }

    public function viewAction()
    {
        $blog    = $this->getBlog();
        $id      = $this->params('article');
        $article = $this->getArticleRepository()->findArticle($blog, $id);

        if (null === $article) {
            throw new Exception\ArticleNotFoundException(sprintf(
                'Article id "%s" not found', $id
            ));
        }

        $slugifier = new Slugifier;
        $slug      = $slugifier->slugify($article->getTitle());
        if ($slug !== $this->params('slug') ) {
            return $this->redirect()->toRoute(null, array(
                'article' => $article->getId(),
                'slug'    => $slug,
            ))->setStatusCode(301);
        }

        return array(
            'article' => $article,
        );
    }

    public function archiveAction()
    {
        $blog      = $this->getBlog();
        $page      = $this->params('page');
        $limit     = $this->getOptions()->getArchiveListingLimit();
        $paginator = $this->getArticleRepository()->findListing($blog, $page, $limit);

        return array(
            'paginator' => $paginator
        );
    }

    public function feedAction()
    {
        $blog     = $this->getBlog();
        $limit    = $this->getOptions()->getFeedListingLimit();
        $articles = $this->getArticleRepository()->findRecent($blog, $limit);

        $model = new FeedModel;
        $model->setOption('feed_type', $this->params('type', 'rss'));

        // Convert articles listing into feed
        $page = $this->getPage();
        $model->title       = $page->getMetaData()->getDescriptiveTitle();
        $model->description = $page->getMetaData()->getDescription() ?: sprintf(self::DEFAULT_FEED_DESCRIPTION, $page->getMetaData()->getTitle());
        $model->link        = $this->url()->fromRoute('/', array(), array('force_canonical' => true));
        $model->feed_link   = array(
            'link' => $this->url()->fromRoute('/feed', array(), array('force_canonical' => true)),
            'type' => $this->params('type', 'rss'),
        );

        if (null !== ($generator = $this->getOptions()->getFeedGenerator())) {
            $model->generator = $generator;
        }

        $entries   = array();
        $modified  = new DateTime('@0');
        $slugifier = new Slugifier;
        foreach ($articles as $article) {
            $entry = array(
                'title'        => $article->getTitle(),
                'description'  => $article->getLead(),
                'date_created' => $article->getPublishDate(),
                'link'         => $this->url()->fromRoute(
                    '/view',
                    array('article' => $article->getId(), 'slug' => $slugifier->slugify($article->getTitle())),
                    array('force_canonical' => true)
                ),
        //        author' => array(
        //             'name'  => 'Jurian Sluiman',
        //             'email' => 'jurian@juriansluiman.nl', // optional
        //             'uri'   => 'http://juriansluiman.nl', // optiona;
        //         ),
            );

            if ($article->getPublishDate() > $modified) {
                $modified = $article->getPublishDate();
            }

            $entries[] = $entry;
        }
        $model->entries       = $entries;
        $model->date_modified = $modified;

        return $model;
    }

    public function byDateAction()
    {
        $blog = $this->getBlog();
        $from = new DateTime($this->params('from'));
        $to   = new DateTime($this->params('to'));
        $now  = new DateTime;

        if ($from > $now || $to > $now) {
            throw new Exception\InvalidArgumentException(
                'The start and end dates must be in the past'
            );
        }

        if ($from > $to) {
            throw new Exception\InvalidArgumentException(
                'The start date must be before the ending date'
            );
        }

        $articles = $this->getArticleRepository()->findByRange($blog, $from, $to);

        return array(
            'from'     => $from,
            'to'       => $to,
            'articles' => $articles,
        );
    }

    protected function getBlog()
    {
        $page = $this->getPage();
        $id   = $page->getModuleId();
        $blog = $this->getBlogRepository()->find($id);

        if (null === $blog) {
            throw new Exception\BlogNotFoundException(sprintf(
                'Cannot find a blog with id "%s"', $id
            ));
        }

        return $blog;
    }

    protected function getPage()
    {
        return $this->getEvent()->getParam('page');
    }
}