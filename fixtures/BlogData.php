<?php
namespace Soflomo\Blog\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Soflomo\Blog\Entity\Blog;

class BlogData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $blog = new Blog;
        $blog->setSlug('default');

        $manager->persist($blog);
        $manager->flush();

        $this->addReference('blog', $blog);
    }
}