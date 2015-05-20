<?php
/**
 * Copyright (c) 2013 Soflomo.
 * All rights reserved.
 *
 * This license allows for redistribution, commercial and non-commercial, as
 * long as it is passed along unchanged and in whole, with credit to Soflomo.
 *
 * @author      Jurian Sluiman <jurian@soflomo.com>
 * @copyright   2013 Soflomo.
 * @license     http://creativecommons.org/licenses/by-nd/3.0/  CC-BY-ND-3.0
 * @link        http://soflomo.com
 */

namespace Soflomo\Blog\Factory;

use Soflomo\Blog\View\Helper\BlogArticleListing;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class BlogArticleListingFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $blogRepository    = $sl->getServiceLocator()->get('Soflomo\Blog\Repository\Blog');
        $articleRepository = $sl->getServiceLocator()->get('Soflomo\Blog\Repository\Article');

        $helper = new BlogArticleListing($blogRepository, $articleRepository);
        return $helper;
    }
}
