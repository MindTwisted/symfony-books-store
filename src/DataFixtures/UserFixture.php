<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixture extends BaseFixture
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(10, 'main_users', function($i) {
            $user = new User();
            $user->setName($this->faker->firstName);
            $user->setEmail(sprintf('user%d@example.com', $i));
            $user->setPassword($this->passwordEncoder->encodePassword($user, 'secret'));
            $user->setApiToken(md5(random_bytes(10)));
            $user->setRoles(['ROLE_USER']);

            return $user;
        });

        $this->createMany(1, 'admin_users', function($i) {
            $user = new User();
            $user->setName($this->faker->firstName);
            $user->setEmail(sprintf('admin%d@example.com', $i));
            $user->setPassword($this->passwordEncoder->encodePassword($user, 'secret'));
            $user->setApiToken(md5(random_bytes(10)));
            $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

            return $user;
        });

        $manager->flush();
    }
}
