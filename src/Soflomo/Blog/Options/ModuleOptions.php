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
 * @subpackage  Options
 * @author      Jurian Sluiman <jurian@soflomo.com>
 * @copyright   2013 Jurian Sluiman.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://soflomo.com
 * @version     @@PACKAGE_VERSION@@
 */

namespace Soflomo\Blog\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    /**
     * Turn off strict options mode
     */
    protected $__strictMode__ = false;

    /**
     * @var int
     */
    protected $recentListingLimit = 10;

    /**
     * @var int
     */
    protected $archiveListingLimit = 10;

    /**
     * @var  int
     */
    protected $feedListingLimit = 10;

    /**
     * @var  int
     */
    protected $adminListingLimit = 10;

    /**
     * @var  array
     */
    protected $feedGenerator;

    /**
     * @var string
     */
    protected $blogEntityClass;

    /**
     * @var string
     */
    protected $articleEntityClass;

    /**
     * Getter for recentListingLimit
     *
     * @return int
     */
    public function getRecentListingLimit()
    {
        return $this->recentListingLimit;
    }

    /**
     * Setter for recentListingLimit
     *
     * @param int $recentListingLimit Value to set
     * @return self
     */
    public function setRecentListingLimit($recentListingLimit)
    {
        $this->recentListingLimit = (int) $recentListingLimit;
        return $this;
    }

    /**
     * Getter for archiveListingLimit
     *
     * @return mixed
     */
    public function getArchiveListingLimit()
    {
        return $this->archiveListingLimit;
    }

    /**
     * Setter for archiveListingLimit
     *
     * @param mixed $archiveListingLimit Value to se
     * @return self
     */
    public function setArchiveListingLimit($archiveListingLimit)
    {
        $this->archiveListingLimit = $archiveListingLimit;
        return $this;
    }

    /**
     * Getter for feedListingLimit
     *
     * @return mixed
     */
    public function getFeedListingLimit()
    {
        return $this->feedListingLimit;
    }

    /**
     * Setter for feedListingLimit
     *
     * @param mixed $feedListingLimit Value to set
     * @return self
     */
    public function setFeedListingLimit($feedListingLimit)
    {
        $this->feedListingLimit = $feedListingLimit;
        return $this;
    }

    /**
     * Getter for adminListingLimit
     *
     * @return mixed
     */
    public function getAdminListingLimit()
    {
        return $this->adminListingLimit;
    }

    /**
     * Setter for adminListingLimit
     *
     * @param mixed $adminListingLimit Value to set
     * @return self
     */
    public function setAdminListingLimit($adminListingLimit)
    {
        $this->adminListingLimit = $adminListingLimit;
        return $this;
    }

    /**
     * Getter for feedGenerator
     *
     * @return mixed
     */
    public function getFeedGenerator()
    {
        return $this->feedGenerator;
    }

    /**
     * Setter for feedGenerator
     *
     * @param mixed $feedGenerator Value to set
     * @return self
     */
    public function setFeedGenerator($feedGenerator)
    {
        if (!is_array($feedGenerator)) {
            throw new InvalidArgumentException(sprintf(
                'Feed generator must be an array, %s given',
                gettype($feedGenerator)
            ));
        }
        if (!isset($feedGenerator['name'])
         || !isset($feedGenerator['version'])
         || !isset($feedGenerator['uri']))
        {
            throw new InvalidArgumentException(sprintf(
                'Feed generator must contain keys "name", "version" and "uri", only "%s" given',
                implode(',', array_keys($feedGenerator))
            ));
        }

        $this->feedGenerator = $feedGenerator;
        return $this;
    }

    /**
     * Getter for blogEntityClass
     *
     * @return mixed
     */
    public function getBlogEntityClass()
    {
        return $this->blogEntityClass;
    }

    /**
     * Setter for blogEntityClass
     *
     * @param mixed $blogEntityClass Value to set
     * @return self
     */
    public function setBlogEntityClass($blogEntityClass)
    {
        $this->blogEntityClass = $blogEntityClass;
        return $this;
    }

    /**
     * Getter for articleEntityClass
     *
     * @return mixed
     */
    public function getArticleEntityClass()
    {
        return $this->articleEntityClass;
    }

    /**
     * Setter for articleEntityClass
     *
     * @param mixed $articleEntityClass Value to set
     * @return self
     */
    public function setArticleEntityClass($articleEntityClass)
    {
        $this->articleEntityClass = $articleEntityClass;
        return $this;
    }
}