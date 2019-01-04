<?php

namespace App\DataFixtures;

use App\Entity\Book;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class BookFixture extends BaseFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(1000, 'books', function($i) {
            $book = new Book();
            $book->setTitle($this->faker->unique()->words($nb = 3, $asText = true));
            $book->setDescription($this->faker->words($nb = 100, $asText = true));
            $book->setPrice($this->faker->randomNumber($nbDigits = 2, $strict = false));
            $book->setDiscount($this->faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 50));
            $book->addAuthor($this->getRandomReference('authors'));
            $book->addAuthor($this->getRandomReference('authors'));
            $book->addGenre($this->getRandomReference('genres'));
            $book->addGenre($this->getRandomReference('genres'));
            $book->addGenre($this->getRandomReference('genres'));

            return $book;
        });

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            AuthorFixture::class,
            GenreFixture::class,
        ];
    }
}
