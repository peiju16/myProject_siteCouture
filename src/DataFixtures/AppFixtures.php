<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;



class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i=0; $i < 10 ; $i++) { 
            $product = new Product;
            $product->setName('création '.$i);
            $product->setDescription($faker->sentence());
            $product->setPrice($faker->randomFloat(1, 10, 100));
            $product->setStock($faker->numberBetween(1, 10));
            $manager->persist($product);
        }

        $manager->flush();
    }
}
