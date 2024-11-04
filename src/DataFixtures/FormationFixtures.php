<?php

namespace App\DataFixtures;

use App\Entity\Formation;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class FormationFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
         $formation = new Formation();
         $formation->setName('Apprendre à faire ourlet simple');
         $formation->setDescription('Un cours de 1h en petit group pour vous apprendre à faire simple ourlet de pantalon par vous-meme!');
         $formation->setDate(new DateTime('14-11-2024 10:00:00'));
         $formation->setNbrPlace(5);
         $formation->setPrice(20);
         $manager->persist($formation);

         $formation2 = new Formation();
         $formation2->setName('Apprendre à réparer vos vêtements');
         $formation2->setDescription('Un cours de 2h en petit groupe pour apprendre à réparer vos vêtements troués ou simplement recoudre des boutons vous-même, au lieu de les jeter !');
         $formation2->setDate(new DateTime('12-12-2024 10:00:00'));
         $formation2->setNbrPlace(5);
         $formation2->setPrice(50);
         $manager->persist($formation2);

        $manager->flush();

        
    }

    public static function getGroups(): array
     {
        return ['formation'];
    }
}
