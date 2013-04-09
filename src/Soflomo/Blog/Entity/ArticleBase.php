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
 * @subpackage  Entity
 * @author      Jurian Sluiman <jurian@soflomo.com>
 * @copyright   2013 Jurian Sluiman.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://soflomo.com
 * @version     @@PACKAGE_VERSION@@
 */

namespace Soflomo\Blog\Entity;

use DateTime;
use Soflomo\Blog\Exception\InvalidArgumentException;

class ArticleBase implements ArticleInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;
    protected $lead;
    protected $body;

    /**
     * @var DateTime
     */
    protected $publishDate;

    protected $blog;
    protected $categories = array();

    /**
     * Getter for id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Getter for title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Setter for title
     *
     * @param string $title Value to set
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = (string) $title;
        return $this;
    }

    /**
     * Getter for lead
     *
     * @return string
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * Setter for lead
     *
     * @param string $lead Value to set
     * @return self
     */
    public function setLead($lead)
    {
        $this->lead = (string) $lead;
        return $this;
    }

    /**
     * Getter for body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Setter for body
     *
     * @param string $body Value to set
     * @return self
     */
    public function setBody($body)
    {
        $this->body = (string) $body;
        return $this;
    }

    /**
     * Getter for publish date
     *
     * @return DateTime
     */
    public function getPublishDate()
    {
        return $this->publishDate;
    }

    /**
     * Setter for publish date
     *
     * @param DateTime|null $publishDate Value to set, null to unset
     * @return self
     */
    public function setPublishDate(DateTime $publishDate = null)
    {
        $this->publishDate = $publishDate;
        return $this;
    }

    /**
     * Check if this article is published
     *
     * @return boolean
     */
    public function isPublished()
    {
        return (null !== $this->publishDate);
    }

    /**
     * Getter for blog
     *
     * @return BlogInterface
     */
    public function getBlog()
    {
        return $this->blog;
    }

    /**
     * Setter for blog
     *
     * @param BlogInterface $blog Value to set
     * @return self
     */
    public function setBlog(BlogInterface $blog)
    {
        $this->blog = $blog;
        return $this;
    }

    // /**
    //  * Getter for categories
    //  *
    //  * @return array
    //  */
    // public function getCategories()
    // {
    //     return $this->categories;
    // }

    // /**
    //  * Setter for categories
    //  *
    //  * @param array $categories Value to set
    //  * @return self
    //  */
    // public function setCategories(array $categories)
    // {
    //     $this->categories = $categories;
    //     return $this;
    // }

    // /**
    //  * Add single category
    //  *
    //  * @param CategoryInterface $category Value to set
    //  * @return self
    //  */
    // public function addCategory(CategoryInterface $category)
    // {
    //     if (!$this->hasCategory($category)) {
    //         $this->categories[] = $category;
    //     }

    //     return $this;
    // }

    // /**
    //  * Remove single category
    //  *
    //  * @param CategoryInterface $category Value to remove
    //  * @return self
    //  */
    // public function removeCategory(CategoryInterface $category)
    // {
    //     $i = null;
    //     foreach ($this->categories as $key => $item) {
    //         if ($item->getId() === $category->getId()) {
    //             $i = $key;
    //             break;
    //         }
    //     }

    //     if (null === $i) {
    //         throw new InvalidArgumentException(sprintf(
    //             'Cannot remove category "%s" as article is not listed in this category',
    //             $category->getId()
    //         ));
    //     }

    //     unset($this->categories[$i]);
    //     return $this;
    // }

    // /**
    //  * Search if article is in given category
    //  *
    //  * @param  CategoryInterface $category Category to search for
    //  * @return boolean                     Whether category is found
    //  */
    // public function hasCategory(CategoryInterface $category)
    // {
    //     foreach ($this->categories as $item) {
    //         if ($item->getId() === $category->getId()) {
    //             return true;
    //         }
    //     }

    //     return false;
    // }
}