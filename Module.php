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
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class Module implements
    Feature\AutoloaderProviderInterface,
    Feature\BootstrapListenerInterface,
    Feature\ConfigProviderInterface,
    Feature\ControllerProviderInterface,
    Feature\ServiceProviderInterface
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

    public function onBootstrap(EventInterface $event)
    {
        $app = $event->getApplication();
        $em  = $app->getEventManager()->getSharedManager();

        $listener    = new InjectTemplateListener;
        $controllers = array(
            'Soflomo\Blog\Controller\ArticleController',
            'Soflomo\BlogAdmin\Controller\ArticleController',
        );
        $em->attach($controllers, MvcEvent::EVENT_DISPATCH, array($listener, 'injectTemplate'), -80);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getControllerConfig()
    {
        return array(
            'factories' => array(
                'Soflomo\Blog\Controller\ArticleController' => function($sm) {
                    $repository = $sm->getServiceLocator()->get('Soflomo\Blog\Repository\Article');
                    $options    = $sm->getServiceLocator()->get('Soflomo\Blog\Options\ModuleOptions');
                    $controller = new Controller\ArticleController($repository, $options);

                    return $controller;
                },

                // ADMIN CONTROLLERS

                'Soflomo\BlogAdmin\Controller\ArticleController' => function($sm) {
                    $service    = $sm->getServiceLocator()->get('Soflomo\BlogAdmin\Service\Article');
                    $form       = $sm->getServiceLocator()->get('Soflomo\BlogAdmin\Form\Article');
                    $options    = $sm->getServiceLocator()->get('Soflomo\Blog\Options\ModuleOptions');
                    $controller = new BlogAdmin\Controller\ArticleController($service, $form, $options);

                    return $controller;
                },
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Soflomo\Blog\Options\ModuleOptions' => function($sm) {
                    $config  = $sm->get('config');
                    $options = new Options\ModuleOptions($config['soflomo_blog']);

                    return $options;
                },
                'Soflomo\Blog\Repository\Article' => function($sm) {
                    $entityManager = $sm->get('Doctrine\ORM\EntityManager');
                    $repository    = $entityManager->getRepository('Soflomo\Blog\Entity\Article');

                    return $repository;
                },
                'Soflomo\Blog\Repository\Blog' => function($sm) {
                    $entityManager = $sm->get('Doctrine\ORM\EntityManager');
                    $repository    = $entityManager->getRepository('Soflomo\Blog\Entity\Blog');

                    return $repository;
                },

                // ADMIN SERVICES

                'Soflomo\BlogAdmin\Form\Article' => function($sm) {
                    $form = new BlogAdmin\Form\Article;
                    $form->setHydrator(new ClassMethodsHydrator);

                    return $form;
                },
                'Soflomo\BlogAdmin\Service\Article' => function($sm) {
                    $em      = $sm->get('Doctrine\ORM\EntityManager');
                    $blog    = $sm->get('Soflomo\Blog\Repository\Blog');
                    $article = $sm->get('Soflomo\Blog\Repository\Article');
                    $service = new BlogAdmin\Service\Article($em, $blog, $article);

                    return $service;
                },
            ),
        );
    }
}