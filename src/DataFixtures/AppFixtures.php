<?php

namespace App\DataFixtures;

use App\Entity\Formation;
use App\Entity\Product;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;



class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $users = [];
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
            $users[] = $user;
            $manager->persist($user);
        }
        

        for ($i=0; $i < 10 ; $i++) { 
            $product = new Product;
            $product->setName('création '.$i);
            $product->setDescription($faker->sentence());
            $product->setPrice($faker->randomFloat(1, 10, 100));
            $stockQuantity = $faker->numberBetween(1, 10); // Define the stock quantity

            $product->setStock($stockQuantity);

                    // Assign users to the product based on stock quantity
            for ($j = 0; $j < $stockQuantity; $j++) {
                // Select a random user for each stock quantity
                $randomUser = $users[mt_rand(0, count($users) - 1)];
                // Add the user to the product
                $product->addUser($randomUser);
            }

            $manager->persist($product);
        }

        $formation = new Formation();
        $formation->setName('Apprendre à faire ourlet simple');
        $formation->setDescription('Un cours de 1h en petit group pour vous apprendre à faire simple ourlet de pantalon par vous-meme!');
        $formation->setDate(new DateTime('14-11-2024 10:00:00'));
        $formation->setNbrPlace(5);
        $formation->setPrice(20);
        $formationUsers = [];
        for ($k = 0; $k < 5; $k++) {
            // Select a random user for each stock quantity
            $formationUser = $users[mt_rand(0, count($users) - 1)];  
            $formation->addUser($formationUser);
        }

       
        $manager->persist($formation);

        $formation2 = new Formation();
        $formation2->setName('Apprendre à réparer vos vêtements');
        $formation2->setDescription('Un cours de 2h en petit groupe pour apprendre à réparer vos vêtements troués ou simplement recoudre des boutons vous-même, au lieu de les jeter !');
        $formation2->setDate(new DateTime('12-12-2024 10:00:00'));
        $formation2->setNbrPlace(5);
        $formation2->setPrice(50);
        $formation2Users = [];
        for ($l = 0; $l < 5; $l++) {
            // Select a random user for each stock quantity
            $formation2User = $users[mt_rand(0, count($users) - 1)];
            $formation2->addUser($formation2User);
        }

        $manager->persist($formation2);

        $manager->flush();
    }
}
