<?php

namespace App\DataFixtures;

use App\Entity\Genre;
use Doctrine\Common\Persistence\ObjectManager;

class GenreFixture extends BaseFixture
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(50, 'genres', function($i) {
            $genre = new Genre();
            $genre->setName($this->faker->unique()->words($nb = 3, $asText = true));

            return $genre;
        });

        $manager->flush();
    }
}
