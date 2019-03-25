<?php

namespace App\DataFixtures;

use App\Entity\Trick;
use App\Entity\TrickGroup;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class TrickFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $tricks = [];
        $trickGroups = [];

        $numberOfTricks = 10;
        $numberOfTrickGroups = 3;

        // Trick groups
        for ($i = 1; $i <= $numberOfTrickGroups; $i++) {
            $trickGroup = new TrickGroup();
            $trickGroup->setName("Groupe n°$i")
                ->setDescription("Description du groupe n°$i");
            $trickGroups[] = $trickGroup;
        }

        // Tricks
        for ($i = 1; $i <= $numberOfTricks; $i++) {
            $trick = new Trick();
            $trick->setName("trick $i")
                ->setDescription("<p>description $i</p>")
                ->setCreatedAt(new DateTime());
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
