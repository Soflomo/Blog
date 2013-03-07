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
 * @package     SoflomoTest\Blog
 * @author      Jurian Sluiman <jurian@soflomo.com>
 * @copyright   2013 Jurian Sluiman.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://soflomo.com
 */
namespace SoflomoTest\Blog\Controller;

use PHPUnit_Framework_TestCase as TestCase;
use Soflomo\Blog\Controller\ArticleController;
use Soflomo\Blog\Entity\Article;
use Soflomo\Blog\Options\ModuleOptions;
use Zend\Mvc\Router\RouteMatch;

class ArticleControllerTest extends TestCase
{
    protected $repository;
    protected $options;
    protected $controller;

    public function setUp()
    {
        $repository = $this->getMockBuilder('Soflomo\Blog\Repository\Article')
                           ->disableOriginalConstructor()
                           ->getMock();
        $options    = new ModuleOptions;
        $controller = new ArticleController($repository, $options);

        $this->repository = $repository;
        $this->options    = $options;
        $this->controller = $controller;
    }

    public function testRecentActionUsesRepository()
    {
        $articles = array('foo', 'bar', 'baz');
        $this->repository->expects($this->once())
                         ->method('findRecent')
                         ->will($this->returnValue($articles));

        $result = $this->controller->recentAction();
        $this->assertEquals(array('articles' => $articles), $result);
    }

    public function testRecentActionUsesLimit()
    {
        $limit = 2;
        $this->options->setRecentListingLimit($limit);

        $this->repository->expects($this->once())
                         ->method('findRecent')
                         ->with($this->equalTo($limit));

        $this->controller->recentAction();
    }

    public function testViewActionUsesRepository()
    {
        $article = new Article;
        $this->repository->expects($this->once())
                         ->method('find')
                         ->with($this->equalTo('1'))
                         ->will($this->returnValue($article));

        $this->controller->getEvent()->setRouteMatch(new RouteMatch(array(
            'article' => '1'
        )));

        $result = $this->controller->viewAction();
        $this->assertEquals(array('article' => $article), $result);
    }

    public function testViewActionThrowsExceptionWhenArticleNotFound()
    {
        $this->setExpectedException('Soflomo\Blog\Exception\ArticleNotFoundException');

        $this->repository->expects($this->once())
                         ->method('find')
                         ->with($this->equalTo('1'))
                         ->will($this->returnValue(null));

        $this->controller->getEvent()->setRouteMatch(new RouteMatch(array(
            'article' => '1'
        )));

        $result = $this->controller->viewAction();
    }
}
