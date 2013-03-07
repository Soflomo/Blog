<?php
namespace Soflomo\Blog\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Faker;
use Soflomo\Blog\Entity\Article;

class ArticleData extends AbstractFixture
{
    protected $n = 10;

    public function load(ObjectManager $manager)
    {
        $blog  = $this->getReference('blog');
        $faker = Faker\Factory::create('nl_NL');

        for ($i=1; $i<=$this->n; $i++) {
            $article = new Article;

            $article->setTitle($faker->sentence(rand(3,6)));  // Words
            $article->setLead($faker->paragraph(rand(6,8)));  // Sentences
            $article->setBody($faker->paragraph(rand(6,10))); // Sentences

            if (!$faker->boolean(10)) {
                // For 9/10 cases post has publish date
                $article->setPublishDate($faker->dateTimeThisMonth);
            }
            $article->setBlog($blog);

            $manager->persist($article);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            'Soflomo\Blog\Fixture\BlogData',
        );
    }
}