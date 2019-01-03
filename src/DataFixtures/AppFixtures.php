<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Faker\Factory;
use App\Entity\User;
use App\Entity\Book;
use App\Entity\Author;
use App\Entity\Genre;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        $user = new User();
        $user->setName('John Walker');
        $user->setEmail('john@example.com');
        $user->setPassword($this->passwordEncoder->encodePassword($user, 'secret'));
        $user->setApiToken(md5(random_bytes(10)));
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $manager->persist($user);

        $user = new User();
        $user->setName('William Smith');
        $user->setEmail('smith@example.com');
        $user->setPassword($this->passwordEncoder->encodePassword($user, 'secret'));
        $user->setApiToken(md5(random_bytes(10)));
        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);

        $books = [];
        $authors = [];
        $genres = [];

        for ($i = 0; $i < 1000; $i++)
        {
            $book = new Book();
            $book->setTitle($faker->unique()->words($nb = 3, $asText = true));
            $book->setDescription($faker->words($nb = 400, $asText = true));
            $book->setPrice($faker->randomNumber($nbDigits = 2, $strict = false));
            $book->setDiscount($faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 50));
            $books[] = $book;
            $manager->persist($book);
        }

        for ($i = 0; $i < 400; $i++)
        {
            $author = new Author();
            $author->setName($faker->unique()->name());
            $authors[] = $author;
            $manager->persist($author);
        }

        for ($i = 0; $i < 50; $i++)
        {
            $genre = new Genre();
            $genre->setName($faker->unique()->words($nb = 3, $asText = true));
            $genres[] = $genre;
            $manager->persist($genre);
        }
        
        foreach($books as $book)
        {
            $book->addAuthor($authors[array_rand($authors)]);
            $book->addAuthor($authors[array_rand($authors)]);

            $book->addGenre($genres[array_rand($genres)]);
            $book->addGenre($genres[array_rand($genres)]);
            $book->addGenre($genres[array_rand($genres)]);
        }

        $manager->flush();
    }
}
