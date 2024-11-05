<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
           $faker = Factory::create('fr_FR');
           
            for ($i=0; $i < 10 ; $i++) { 
                $user = new User();
                $user->setFirstname($faker->firstName);
                $user->setLastname($faker->lastName);
                $user->setTelephone($faker->phoneNumber);
                $user->setAddress($faker->streetAddress);
                $user->setZipcode($faker->postcode);
                $user->setCity($faker->city);
                $user->setEmail($faker->email);
                $user->setPleinPassword('password'); 
                $user->setRoles(['ROLE_USER']);
                $user->setPseudo((mt_rand(0,1) === 1) ? $faker->firstName : 'null');
                // $user->setPicture($faker->image($dir = null, $width = 320, $height = 240));
                $manager->persist($user);
            }
            
            // $product = new Product();
            // $manager->persist($product);
    
            $manager->flush();
        }

}
