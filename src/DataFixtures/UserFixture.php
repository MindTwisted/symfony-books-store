<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\ApiToken;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixture extends BaseFixture
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(10, 'main_users', function($i) use ($manager) {
            $user = new User();
            $user->setName($this->faker->firstName);
            $user->setEmail(sprintf('user%d@example.com', $i));
            $user->setPassword($this->passwordEncoder->encodePassword($user, 'secret'));
            
            $token = new ApiToken($user);

            $manager->persist($token);

            return $user;
        });

        $this->createMany(1, 'admin_users', function($i) use ($manager) {
            $user = new User();
            $user->setName($this->faker->firstName);
            $user->setEmail(sprintf('admin%d@example.com', $i));
            $user->setPassword($this->passwordEncoder->encodePassword($user, 'secret'));
            $user->setRoles(['ROLE_ADMIN']);
            
            $token = new ApiToken($user);

            $manager->persist($token);

            return $user;
        });

        $manager->flush();
    }
}
