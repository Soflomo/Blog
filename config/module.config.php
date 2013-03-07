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

return array(
    'soflomo_blog' => array(
        'recent_listing_limit' => 10,
    ),

    'router' => array(
        'routes' => array(
            'blog' => array(
                'type'    => 'literal',
                'options' => array(
                    'route'    => '/blog',
                    'defaults' => array(
                        'controller' => 'Soflomo\Blog\Controller\ArticleController',
                        'action'     => 'recent',
                    ),
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'view' => array(
                        'type'    => 'segment',
                        'options' => array(
                            'route'    => '/:article[/:slug]',
                            'defaults' => array(
                                'action' => 'view',
                            ),
                            'constraints' => array(
                                'article' => '[0-9]+',
                                'slug'    => '[a-zA-Z0-9-_]+',
                            ),
                        ),
                    ),
                    'by-date' => array(
                        'type'    => 'segment',
                        'options' => array(
                            'route'    => '/:from/:to',
                            'defaults' => array(
                                'action' => 'by-date',
                            ),
                            'constraints' => array(
                                'from' => '[0-9]{2}-[0-9]{2}-[0-9]{4}',
                                'to'   => '[0-9]{2}-[0-9]{2}-[0-9]{4}',
                            ),
                        ),
                    ),
                ),
            ),

            'zfcadmin' => array(
                'child_routes' => array(
                    'blog' => array(
                        'type'    => 'segment',
                        'options' => array(
                            'route'    => '/blog/:blog',
                            'defaults' => array(
                                'controller' => 'Soflomo\BlogAdmin\Controller\ArticleController',
                                'action'     => 'index',
                            ),
                            'constraints' => array(
                                'blog' => '[a-zA-Z0-9-_]+'
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes'  => array(
                            'article' => array(
                                'type'    => 'segment',
                                'options' => array(
                                    'route' => '/article'
                                ),
                                'may_terminate' => false,
                                'child_routes'  => array(
                                    'view' => array(
                                        'type'    => 'segment',
                                        'options' => array(
                                            'route' => '/:article',
                                            'defaults' => array(
                                                'action' => 'view',
                                            ),
                                            'constraints' => array(
                                                'article' => '[0-9]+'
                                            ),
                                        ),
                                    ),
                                    'create' => array(
                                        'type'    => 'literal',
                                        'options' => array(
                                            'route' => '/new',
                                            'defaults' => array(
                                                'action' => 'create',
                                            ),
                                        ),
                                    ),
                                    'update' => array(
                                        'type'    => 'segment',
                                        'options' => array(
                                            'route' => '/:article/edit',
                                            'defaults' => array(
                                                'action' => 'update',
                                            ),
                                            'constraints' => array(
                                                'article' => '[0-9]+'
                                            ),
                                        ),
                                    ),
                                    'delete' => array(
                                        'type'    => 'segment',
                                        'options' => array(
                                            'route' => '/:article/delete',
                                            'defaults' => array(
                                                'action' => 'delete',
                                            ),
                                            'constraints' => array(
                                                'article' => '[0-9]+'
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),

    'navigation' => array(
        'admin' => array(
            'blog' => array(
                'label'  => 'Blog',
                'route'  => 'zfcadmin/blog',
                'params' => array('blog' => 'default'),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),

    'doctrine' => array(
        'driver' => array(
            'soflomo_blog' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\XmlDriver',
                'paths' => __DIR__ . '/mapping'
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Soflomo\Blog\Entity' => 'soflomo_blog'
                ),
            ),
        ),
    ),

    'data-fixture' => array(
        'soflomo-blog' => __DIR__ . '/../fixtures',
    ),
);