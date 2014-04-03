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
 * @subpackage  Repository
 * @author      Jurian Sluiman <jurian@soflomo.com>
 * @copyright   2013 Jurian Sluiman.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://soflomo.com
 * @version     @@PACKAGE_VERSION@@
 */

namespace Soflomo\Blog\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

use Soflomo\Blog\Entity\Blog                              as BlogEntity;

use Doctrine\ORM\Tools\Pagination\Paginator               as DoctrinePaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use Zend\Paginator\Paginator;

class Article extends EntityRepository
{
    public function findRecent(BlogEntity $blog, $limit)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->andWhere('a.blog = :blog')
           ->setParameter('blog', $blog)
           ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function findArticle(BlogEntity $blog, $id)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->andWhere('a.blog = :blog')
           ->setParameter('blog', $blog)
           ->andWhere('a.id = :id')
           ->setParameter('id', $id);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findListing(BlogEntity $blog, $page, $limit, $includeUnpublished = false)
    {
        $qb = $this->createQueryBuilder('a', !$includeUnpublished);
        $qb->andWhere('a.blog = :blog')
           ->setParameter('blog', $blog);

        if (true === $includeUnpublished) {
            $qb->andWhere('a.publishDate IS NOT NULL');
            $qb->orderBy('a.publishDate', 'DESC');
        }

        $paginator = $this->getPaginator($qb->getQuery());
        $paginator->setCurrentPageNumber($page)
                  ->setItemCountPerPage($limit);

        return $paginator;
    }

    public function findCategoryListing(BlogEntity $blog, $category, $page, $limit, $includeUnpublished = false)
    {
        $qb = $this->createQueryBuilder('a', !$includeUnpublished);
        $qb->andWhere('a.blog = :blog')
           ->setParameter('blog', $blog)
           ->leftJoin('a.category', 'c')
           ->andWhere('c.slug = :category')
           ->setParameter('category', $category);

        if (true === $includeUnpublished) {
            $qb->andWhere('a.publishDate IS NOT NULL');
            $qb->orderBy('a.publishDate', 'DESC');
        }

        $paginator = $this->getPaginator($qb->getQuery());
        $paginator->setCurrentPageNumber($page)
                  ->setItemCountPerPage($limit);

        return $paginator;
    }

    public function findUnpublished(BlogEntity $blog)
    {
        $qb = $this->createQueryBuilder('a', false);
        $qb->andWhere('a.blog = :blog')
           ->setParameter('blog', $blog)
           ->andWhere('a.publishDate IS NULL')
           ->orderBy('a.id');

        return $qb->getQuery()->getResult();
    }

    public function getPaginator(Query $query)
    {
        $paginator = new DoctrinePaginator($query);
        $adapter   = new PaginatorAdapter($paginator);

        return new Paginator($adapter);
    }

    public function findByRange(BlogEntity $blog, DateTime $from, DateTime $to)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->andWhere('a.blog = :blog')
           ->setParameter('blog', $blog)
           ->andWhere('a.publishDate > :from')
           ->setParameter('from', $from)
           ->andWhere('a.publishDate < :to')
           ->setParameter('to', $to);

        return $qb->getQuery()->getResult();
    }

    public function createQueryBuilder($alias, $constraints = true)
    {
        $qb = parent::createQueryBuilder($alias);

        if (true === $constraints) {
            $qb->andWhere('a.publishDate IS NOT NULL')
               ->andWhere('a.publishDate <= :now')
               ->setParameter('now', new DateTime)
               ->orderBy('a.publishDate', 'DESC');
        }

        return $qb;
    }
}