<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\Member;
use App\Entity\Trick;
use App\Entity\TrickGroup;
use App\Entity\Video;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use Faker\Factory;

class MemberFixtures extends Fixture
{
    private $faker;
    private $membersCount = 20;
    private $tricksCount = 100;
    private $trickGroupsCount = 10;
    private $imagesPerTrick = [2, 7];
    private $videosPerTrick = [2, 5];
    private $commentsPerTrick = [20, 50];
    private $manager;
    private $members = [];
    private $tricks = [];
    private $trickGroups = [];
    private $videos = [];
    private $realisticFixtures = true; // Set this boolean to true to load realistic fixtures

    private const OFFSET_ROTATIONS = [
        "Cork",
        "Rodeo",
        "Misty"
    ];

    /**
     * Generate members along with their tricks and comments
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->faker = Factory::create('fr_FR');

        // Members
        $this->generateMembers();

        // Trick groups
        if ($this->realisticFixtures) {
            $this->generateTrickGroups();
        } else {
            $this->generateDummyTrickGroups();
        }

        // Tricks
        if ($this->realisticFixtures) {
            for ($i = 1; $i <= $this->tricksCount; $i++) {
                $this->tricks[] = $this->generateTrick();
            }
        } else {
            for ($i = 1; $i <= $this->tricksCount; $i++) {
                $this->tricks[] = $this->generateDummyTrick($i);
            }
        }

        // Videos
        $this->addVideosToTricks();

        // Link tricks and trick groups
        if (!$this->realisticFixtures) {
            for ($i = 0; $i < $this->tricksCount; $i++) {
                for ($j = 0; $j < mt_rand(0, $this->trickGroupsCount - 1); $j++) {
                    $trickGroupKey = mt_rand(0, $this->trickGroupsCount - 1);
                    $this->tricks[$i]->addTrickGroup($this->trickGroups[$trickGroupKey]);
                    $this->trickGroups[$trickGroupKey]->addTrick($this->tricks[$i]);
                }
                $this->manager->persist($this->tricks[$i]);
            }

            foreach ($this->trickGroups as $trickGroup) {
                $this->manager->persist($trickGroup);
            }
        }

        $this->manager->flush();
    }

    // Realistic fixtures

    private function generateTrickGroups()
    {
        $trickGroupJsonData = file_get_contents(dirname(dirname(__DIR__)) . "/demodata/groups.json");
        $trickGroupData = \json_decode($trickGroupJsonData);
        $this->trickGroupsCount = count($trickGroupData);

        foreach ($trickGroupData as $trickGroupDatum) {
            $trickGroup = new TrickGroup();
            $trickGroup->setName($trickGroupDatum->name)
                ->setDescription($trickGroupDatum->description);
            $this->manager->persist($trickGroup);
            $this->trickGroups[] = $trickGroup;
        }
    }

    private function generateTrick()
    {
        $trick = new Trick();
        $trick->setName($this->generateTrickName())
            ->setDescription($this->generateDescription($trick->getName()))
            ->setMainImage($this->addRealisticImage()->getUrl())
            ->setCreatedAt($this->faker->dateTimeThisYear());

        // Images
        for ($i = 0, $count = mt_rand($this->imagesPerTrick[0], $this->imagesPerTrick[1]); $i < $count; $i++) {
            $image = $this->addRealisticImage();
            $trick->addImage($image);
            $image->setTrick($trick);
            $this->manager->persist($image);
        }

        // Author
        $trick->setAuthor($this->members[
        mt_rand(0, $this->membersCount - 1)
        ]);

        // Comments
        $this->addCommentsToTrick($trick);

        // TrickGroups
        $trickParts = explode(" ", $trick->getName());
        $rotationTrickGroup = $this->getTrickGroupFromName("Rotation");
        $offsetRotationTrickGroup = $this->getTrickGroupFromName("Rotation désaxée");

        foreach ($trickParts as $trickPart) {
            if (is_numeric($trickPart)) {
                $this->linkTrickToTrickGroup($trick, $rotationTrickGroup);
            } elseif (in_array($trickPart, self::OFFSET_ROTATIONS)) {
                $this->linkTrickToTrickGroup($trick, $offsetRotationTrickGroup);
            } else {
                foreach ($this->trickGroups as $trickGroup) {
                    if (strtolower($trickPart) === strtolower($trickGroup->getName())) {
                        $this->linkTrickToTrickGroup($trick, $trickGroup);
                        break;
                    }
                }
            }
        }

        $this->manager->persist($trick);

        return $trick;
    }

    private function generateTrickName()
    {
        $addJump = function (array &$nameParts) {

            // Switch
            if (mt_rand(0, 2) === 1) {
                $nameParts[] = "Switch";
            }

            // Offset rotation
            if (mt_rand(0, 2) === 1) {
                $nameParts[] = self::OFFSET_ROTATIONS[mt_rand(0, 2)];
            }

            // Rotation
            $nameParts[] = mt_rand(1, 6) * 180;
        };

        $addSlide = function (array &$nameParts, bool $beginWithJump, bool $finishWithJump) {

            if ($beginWithJump) {
                $nameParts[] = "To";
            }

            if (mt_rand(0, 1) === 1) {
                $nameParts[] = "Nose slide";
            } elseif (mt_rand(0, 1) === 1) {
                $nameParts[] = "Tail slide";
            } else {
                $nameParts[] = "Rail";
            }

            if ($finishWithJump) {
                $nameParts[] = "To";
            }
        };

        $nameParts = [];
        $beginWithJump = false;
        $finishWithJump = false;

        if (mt_rand(0, 2) > 0) {
            $beginWithJump = true;
            $addJump($nameParts);
        }

        if (mt_rand(0, 3) === 1 || !$beginWithJump) {
            if (mt_rand(0, 2) === 0) {
                $finishWithJump = true;
            }
            $addSlide($nameParts, $beginWithJump, $finishWithJump);
            if ($finishWithJump) {
                $addJump($nameParts);
            }
        }

        $trickName = implode(" ", $nameParts);

        foreach ($this->tricks as $trick) {
            if ($trick->getName() === $trickName) {
                $trickName = $this->generateTrickName();
                break;
            }
        }

        return $trickName;
    }

    private function generateDescription(string $trickName)
    {
        $descriptionParts = [];
        $trickParts = explode(" ", $trickName);
        $beginnings = [
            "<p>Voici la recette :</p>",
            "<p>Bon, alors c'est tout simple, il suffit de :</p>",
            "<p>Attention les yeux ! Voici comment ça se danse :</p>",
        ];
        $endings = [
            "<p>Et voilà !</p>",
            "<p>Alors, tu tentes le coup ?</p>",
            "<p>Enorme !!!</p>",
            "<p>Un trick facile, pour les débutants.</p>",
            "<p>Un trick de malade !</p>"
        ];

        $descriptionParts[] = $beginnings[mt_rand(0, count($beginnings) - 1)];

        for ($i = 0, $size = count($trickParts); $i < $size; $i++) {
            if ($trickParts[$i] === "Switch") {
                $descriptionParts[] = "<li>Tu te place en regular si tu est goofy et inversement</li>";
            } elseif (preg_match("/[0-9]+/", $trickParts[$i])) {
                $descriptionParts[] = "<li>Tu pivotes de {$trickParts[$i]} degrés</li>";
            } elseif ($trickParts[$i] === "Rail") {
                $descriptionParts[] = "<li>Tu glisse sur la barre</li>";
            } elseif ($trickParts[$i] === "Nose") {
                $descriptionParts[] = "<li>Tu glisse sur la barre avec l'avant de ta planche</li>";
            } elseif ($trickParts[$i] === "Tail") {
                $descriptionParts[] = "<li>Tu glisse sur la barre avec l'arrière de ta planche</li>";
            }
        }

        return "<ul>" . implode("", $descriptionParts) . "</ul><p>" . implode("</p><p>", $this->faker->paragraphs(mt_rand(3, 5))) . "</p>" . $endings[mt_rand(0, count($endings) - 1)];
    }

    private function addRealisticImage()
    {
        $url = "/img/tricks/";
        $files = scandir(dirname(dirname(__DIR__)) . '/public' . $url);
        $image = new Image();
        $image->setUrl($url . $files[mt_rand(2, count($files) - 1)]);

        return $image;
    }

    private function addVideosToTricks()
    {
        $videoUrls = file(dirname(dirname(__DIR__)) . "/demodata/youtube_videos");
        $numberOfVideos = count($videoUrls);
        $k = 0;

        foreach ($videoUrls as $videoUrl) {
            $video = new Video();
            $video->setUrl($videoUrl);
            $this->videos[] = $video;
        }

        for ($i = 0; $i < $this->tricksCount; $i++) {
            if ($k < $numberOfVideos) {
                for ($j = 0, $size = mt_rand($this->videosPerTrick[0], $this->videosPerTrick[1]); $j < $size; $j++) {
                    $this->tricks[$i]->addVideo($this->videos[$k]);
                    $this->videos[$k]->setTrick($this->tricks[$i]);
                    $this->manager->persist($this->videos[$k]);
                    $k++;
                    if ($k >= $numberOfVideos) {
                        break;
                    }
                }
            } else {
                $k = 0;
            }
        }
    }

    // Dummy fixtures

    private function generateDummyTrickGroups()
    {
        for ($i = 1; $i <= $this->trickGroupsCount; $i++) {
            $description = "<p>" . implode("</p><p>", $this->faker->paragraphs(mt_rand(1, 2))) . "</p>";
            $trickGroup = new TrickGroup();
            $trickGroup->setName("Groupe n°$i")
                ->setDescription("<h2>Description du groupe n°$i</h2>$description");
            $this->manager->persist($trickGroup);
            $this->trickGroups[] = $trickGroup;
        }
    }

    private function generateMembers()
    {
        for ($i = 0; $i < $this->membersCount; $i++) {
            $member = new Member();

            $firstName = $this->faker->firstName();
            $lastName = $this->faker->lastName;

            $member->setName("$firstName $lastName")
                ->setPassword('$2y$13$qACYre5/bO7y2jW4n8S.m.Es6vjYpz7x8XBhZxBvckcr.VoC5cvqq') // pwdSucks!0
                ->setEmail($this->faker->email)
                ->setRoles([Member::ROLE_USER]);

            if ($i <= 3) {
                if ($i === 1) {
                    $member->setEmail("moderator@snow.com");
                    $member->addRole(Member::ROLE_MODERATOR);
                }

                if ($i === 2) {
                    $member->setEmail("editor@snow.com");
                    $member->addRole(Member::ROLE_EDITOR);
                }

                if ($i === 3) {
                    $member->setEmail("manager@snow.com");
                    $member->addRole(Member::ROLE_MANAGER);
                }

                if ($i === 0) {
                    $member->addRole(Member::ROLE_ADMIN);
                    $member->setEmail("admin@snow.com");
                }
            }

            if ($i === 4) {
                $member->setName("Jim Nastique");
                $member->setEmail("testuser@snow.com");
            }

            // Picture
            if (mt_rand(0, 2)) {
                $picture = $this->newImage(500, 300);
                $member->setPicture($picture);
                $picture->setMember($member);
                $this->manager->persist($picture);
            }
            $this->manager->persist($member);
            $this->members[] = $member;
        }
    }

    private function generateDummyTrick($i)
    {
        $description = $this->generateParagraphs(5, 15);
        $imageWidth = 1024;
        $imageHeight = 768;

        $trick = new Trick();
        $trick->setName("trick $i")
            ->setDescription("<h2>description $i</h2>$description")
            ->setCreatedAt($this->faker->dateTimeThisYear()) // WARNING: this will be override by Trick::setCreatedAtToNow() so comment its content before loading these fixtures
            ->setMainImage($this->faker->imageUrl($imageWidth, $imageHeight));

        // Images
        for ($j = 0, $size = rand($this->imagesPerTrick[0], $this->imagesPerTrick[1]); $j < $size; $j++) {
            $image = self::newImage($imageWidth, $imageHeight);
            $trick->addImage($image);
            $image->setTrick($trick);
            $this->manager->persist($image);
        }

        // Author
        $trick->setAuthor($this->members[
        mt_rand(0, $this->membersCount - 1)
        ]);

        // Comments
        $this->addCommentsToTrick($trick);

        return $trick;
    }

    private function newImage(int $imageWidth, int $imageHeight): Image
    {
        $image = new Image();
        $image->setUrl($this->faker->imageUrl($imageWidth, $imageHeight));

        return $image;
    }

    private function generateParagraphs($min = 1, $max = 1): string
    {
        return "<p>" . implode("</p><p>", $this->faker->paragraphs(mt_rand($min, $max))) . "</p>";
    }

    private function addCommentsToTrick(Trick $trick)
    {
        for ($j = 0, $size = rand($this->commentsPerTrick[0], $this->commentsPerTrick[1]); $j < $size; $j++) {
            $comment = new Comment();
            $comment->setAuthor($this->members[
            mt_rand(0, $this->membersCount - 1)
            ])
                ->setCreatedAt($this->faker->dateTimeThisYear())
                ->setTrick($trick)
                ->setContent(strip_tags($this->generateParagraphs(1, 3)));
            $trick->addComment($comment);
            $this->manager->persist($comment);
        }
    }

    // Utilities

    private function linkTrickToTrickGroup(Trick $trick, TrickGroup $trickGroup)
    {
        $trick->addTrickGroup($trickGroup);
        $trickGroup->addTrick($trick);
        $this->manager->persist($trickGroup);
    }

    /**
     * Get a trick group from its name
     *
     * @param string $groupName
     * @return mixed
     * @throws Exception
     */
    private function getTrickGroupFromName(string $groupName)
    {
        foreach ($this->trickGroups as $trickGroup) {
            if ($trickGroup->getName() === $groupName) {
                return $trickGroup;
            }
        }
        throw new Exception("$groupName is not a valid trick group name");
    }
}
