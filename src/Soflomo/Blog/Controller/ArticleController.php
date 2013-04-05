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
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\FeedModel;

class ArticleController extends AbstractActionController
{
    /**
     * @var ArticleRepository
     */
    protected $repository;

    /**
     * @var ModuleOptions
     */
    protected $options;

    public function __construct(ArticleRepository $repository, ModuleOptions $options = null)
    {
        $this->repository = $repository;

        if (null !== $options) {
            $this->options = $options;
        }
    }

    public function getRepository()
    {
        return $this->repository;
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
        $limit    = $this->getOptions()->getRecentListingLimit();
        $articles = $this->getRepository()->findRecent($limit);

        return array(
            'articles' => $articles,
        );
    }

    public function viewAction()
    {
        $id      = $this->params('article');
        $article = $this->getRepository()->find($id);

        if (null === $article) {
            throw new Exception\ArticleNotFoundException(sprintf(
                'Article id "%s" not found', $id
            ));
        }

        $now = new DateTime;
        if (!$article->isPublished() || $article->getPublishDate() > $now) {
            throw new Exception\ArticleNotFoundException(sprintf(
                'Article id "%s" is not published', $id
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
        $page      = $this->params('page');
        $limit     = $this->getOptions()->getArchiveListingLimit();
        $paginator = $this->getRepository()->getPaginator();

        $paginator->setCurrentPageNumber($page)
                  ->setItemCountPerPage($limit);

        return array(
            'paginator' => $paginator
        );
    }

    public function feedAction()
    {
        $limit    = $this->getOptions()->getFeedListingLimit();
        $articles = $this->getRepository()->findRecent($limit);

        $model = new FeedModel;
        $model->setOption('feed_type', $this->params('type', 'rss'));

        // Convert articles listing into feed
        $model->title       = 'Soflomo Blog';              // Get ensemble page title
        $model->description = 'Blog for testing purposes'; // Get ensemble page description
        $model->link        = $this->url()->fromRoute('blog', array(), array('force_canonical' => true));
        $model->feed_link   = array(
            'link' => $this->url()->fromRoute('blog/feed', array(), array('force_canonical' => true)),
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
                    'blog/view',
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
        $from = new DateTime($this->params('from'));
        $to   = new DateTime($this->params('to'));

        $articles = $this->getRepository()->findByRange($from, $to);

        return array(
            'from'     => $from,
            'to'       => $to,
            'articles' => $articles,
        );
    }
}