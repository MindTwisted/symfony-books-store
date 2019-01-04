<?php

namespace App\DataFixtures;

use App\Entity\Author;
use Doctrine\Common\Persistence\ObjectManager;

class AuthorFixture extends BaseFixture
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(200, 'authors', function($i) {
            $author = new Author();
            $author->setName($this->faker->unique()->name());

            return $author;
        });

        $manager->flush();
    }
}
