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
 * @subpackage  Service
 * @author      Jurian Sluiman <jurian@soflomo.com>
 * @copyright   2013 Jurian Sluiman.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://soflomo.com
 * @version     @@PACKAGE_VERSION@@
 */

namespace Soflomo\BlogAdmin\Service;

use Soflomo\Blog\Entity\CategoryInterface as CategoryEntity;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository as BlogRepository;
use Doctrine\ORM\EntityRepository as CategoryRepository;
use Zend\EventManager;

class Category implements EventManager\EventManagerAwareInterface
{
    protected $entityManager;
    protected $blogRepository;
    protected $categoryRepository;

    protected $eventManager;

    public function __construct(EntityManager $em, BlogRepository $blogRepository, CategoryRepository $categoryRepository)
    {
        $this->entityManager     = $em;
        $this->blogRepository    = $blogRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function getRepository()
    {
        return $this->getCategoryRepository();
    }

    public function getBlogRepository()
    {
        return $this->blogRepository;
    }

    public function getCategoryRepository()
    {
        return $this->categoryRepository;
    }

    public function getEntityManager()
    {
        return $this->entityManager;
    }

    public function create(CategoryEntity $category)
    {
        $this->trigger(__FUNCTION__ . '.pre', array('category' => $category));

        $this->getEntityManager()->persist($category);
        $this->getEntityManager()->flush();

        $this->trigger(__FUNCTION__ . '.post', array('category' => $category));
    }

    public function update(CategoryEntity $category)
    {
        $this->trigger(__FUNCTION__ . '.pre', array('category' => $category));

        $this->getEntityManager()->flush();

        $this->trigger(__FUNCTION__ . '.post', array('category' => $category));
    }

    public function delete(CategoryEntity $category)
    {
        $this->trigger(__FUNCTION__ . '.pre', array('category' => $category));

        $this->getEntityManager()->remove($category);
        $this->getEntityManager()->flush();

        $this->trigger(__FUNCTION__ . '.post', array('category' => $category));
    }

    public function trigger($name, array $parameters = array())
    {
        $event = new EventManager\Event;
        $event->setTarget($this);
        $event->setName($name);
        $event->setParams($parameters);

        $this->getEventManager()->trigger($event);
    }

    /**
     * Getter for eventManager
     *
     * @return mixed
     */
    public function getEventManager()
    {
        if (null === $this->eventManager) {
            $this->setEventManager(new EventManager\EventManager);
        }
        return $this->eventManager;
    }

    /**
     * Setter for eventManager
     *
     * @param mixed $eventManager Value to set
     * @return self
     */
    public function setEventManager(EventManager\EventManagerInterface $eventManager)
    {
        $eventManager->setIdentifiers(array(
            __CLASS__,
            get_called_class(),
        ));

        $this->eventManager = $eventManager;
        return $this;
    }

}