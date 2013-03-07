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
namespace SoflomoTest\Blog\Repository;

use PHPUnit_Framework_TestCase as TestCase;
use Soflomo\Blog\Entity\Article;
use Soflomo\Blog\Repository\Article as ArticleRepository;

class ArticleRepositoryTest extends TestCase
{
    protected $qb;
    protected $repository;

    public function setUp()
    {
        $em         = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                           ->disableOriginalConstructor()
                           ->getMock();
        $mapping    = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
                            ->disableOriginalConstructor()
                            ->getMock();

        $this->qb   = $this->getMock('Doctrine\ORM\QueryBuilder',
                                     array(), array($em));
        $repository = $this->getMock('Soflomo\Blog\Repository\Article',
                                     array('createQueryBuilder'),
                                     array('foo', $mapping));

        $this->repository = $repository;
        $this->repository->expects($this->any())
                         ->method('createQueryBuilder')
                         ->with($this->equalTo('a'))
                         ->will($this->returnValue($this->qb));
    }

    public function testFindRecentLimitsToGivenLimit()
    {
        $limit = '1';
        $this->qb->expects($this->any())
                 ->method('setMaxResults')
                 ->with($this->equalTo($limit));

        $this->repository->findRecent($limit);
    }

    public function testFindRecentOrdersByPublishDate()
    {
        $this->qb->expects($this->any())
                 ->method('andWhere')
                 ->with($this->equalTo('a.publishDate'), $this->equalTo('DESC'));

        $this->repository->findRecent(1);
    }
}
