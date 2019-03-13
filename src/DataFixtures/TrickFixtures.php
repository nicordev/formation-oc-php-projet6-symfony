<?php

namespace App\DataFixtures;

use App\Entity\Trick;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class TrickFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 10; $i++) {
            $trick = new Trick();
            $trick->setName("trick $i")
                ->setDescription("<p>description $i</p>")
                ->setCreatedAt(new DateTime());
            $manager->persist($trick);
        }

        $manager->flush();
    }
}
