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
 * @author      Jurian Sluiman <jurian@soflomo.com>
 * @copyright   2013 Jurian Sluiman.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://soflomo.com
 * @version     @@PACKAGE_VERSION@@
 */

namespace Soflomo\Blog;

use Soflomo\BlogAdmin;
use Soflomo\Common\View\InjectTemplateListener;

use Zend\ModuleManager\Feature;
use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;

class Module implements
    Feature\AutoloaderProviderInterface,
    Feature\DependencyIndicatorInterface,
    Feature\BootstrapListenerInterface,
    Feature\ConfigProviderInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__           => __DIR__ . '/src/Soflomo/Blog',
                    __NAMESPACE__ . 'Admin' => __DIR__ . '/src/Soflomo/BlogAdmin',
                ),
            ),
        );
    }
    public function getModuleDependencies()
    {
        return array(
            'DoctrineModule',
            'DoctrineORMModule',
            'Soflomo\Common',
            'Soflomo\Purifier',
        );
    }

    public function onBootstrap(EventInterface $event)
    {
        $app = $event->getApplication();
        $sm  = $app->getServiceManager();
        $em  = $app->getEventManager()->getSharedManager();

        $this->attachTemplateListener($em);
        $this->attachFeedStrategy($em, $sm);
        $this->attachNavigationMetadata($em);
    }

    protected function attachTemplateListener($em)
    {
        $listener    = new InjectTemplateListener;
        $controllers = array(
            'Soflomo\Blog\Controller\ArticleController',
            'Soflomo\BlogAdmin\Controller\ArticleController',
        );
        $em->attach($controllers, MvcEvent::EVENT_DISPATCH, array($listener, 'injectTemplate'), -80);
    }

    protected function attachFeedStrategy($em, $sm)
    {
        $controllers = array(
            'Soflomo\Blog\Controller\ArticleController',
        );
        $em->attach($controllers, MvcEvent::EVENT_DISPATCH, function($e) use ($sm) {
            $view         = $sm->get('Zend\View\View');
            $feedStrategy = $sm->get('ViewFeedStrategy');

            $view->getEventManager()->attach($feedStrategy, 10);
        }, 10);
    }

    protected function attachNavigationMetadata($em)
    {
        $em->attach('Ensemble\Kernel\Parser\Navigation', 'parsePage.blog', function($e) {
            $page = $e->getParam('navigation');
            $page->set('changefreq', 'hourly');
        });
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'BlogArticleListing' => function($sl) {
                    $blogRepository    = $sl->getServiceLocator()->get('Soflomo\Blog\Repository\Blog');
                    $articleRepository = $sl->getServiceLocator()->get('Soflomo\Blog\Repository\Article');

                    return new View\Helper\BlogArticleListing($blogRepository, $articleRepository);
                }
            ),
        );
    }
}
