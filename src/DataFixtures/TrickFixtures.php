<?php

namespace App\DataFixtures;

use App\Entity\Trick;
use App\Entity\TrickGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class TrickFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $tricks = [];
        $trickGroups = [];

        $numberOfTricks = 100;
        $numberOfTrickGroups = 10;

        $faker = Factory::create('fr_FR');

        // Trick groups
        for ($i = 1; $i <= $numberOfTrickGroups; $i++) {
            $description = "<p>" . implode("</p><p>", $faker->paragraphs(mt_rand(1, 2))) . "</p>";
            $trickGroup = new TrickGroup();
            $trickGroup->setName("Groupe n°$i")
                ->setDescription("<h2>Description du groupe n°$i</h2>$description");
            $trickGroups[] = $trickGroup;
        }

        // Tricks
        for ($i = 1; $i <= $numberOfTricks; $i++) {
            $description = "<p>" . implode("</p><p>", $faker->paragraphs(mt_rand(3, 5))) . "</p>";
            $trick = new Trick();
            $trick->setName("trick $i")
                ->setDescription("<h2>description $i</h2>$description")
                ->setCreatedAt($faker->dateTimeThisYear())
                ->setMainImage($faker->imageUrl());
            $tricks[] = $trick;
        }

        // Link tricks and trick groups
        for ($i = 0; $i < $numberOfTricks; $i++) {
            for ($j = 0; $j < mt_rand(0, $numberOfTrickGroups - 1); $j++) {
                $trickGroupKey = mt_rand(0, $numberOfTrickGroups - 1);
                $tricks[$i]->addTrickGroup($trickGroups[$trickGroupKey]);
                $trickGroups[$trickGroupKey]->addTrick($tricks[$i]);
            }
            $manager->persist($tricks[$i]);
        }

        for ($i = 0; $i < $numberOfTrickGroups; $i++) {
            $manager->persist($trickGroups[$i]);
        }

        $manager->flush();
    }
}
