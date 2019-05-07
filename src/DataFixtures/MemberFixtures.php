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
use Faker\Factory;

class MemberFixtures extends Fixture
{
    private $faker;

    /**
     * Generate members along with their tricks and comments
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $members = [];
        $tricks = [];
        $trickGroups = [];
        $videos = [];

        $numberOfMembers = 20;
        $numberOfTricks = 100;
        $numberOfTrickGroups = 10;

        $this->faker = Factory::create('fr_FR');

        // Members
        for ($i = 0; $i < $numberOfMembers; $i++) {
            $member = new Member();

            $firstName = $this->faker->firstName();
            $lastName = $this->faker->lastName;

            $member->setName("$firstName $lastName")
                ->setPassword('$2y$13$qACYre5/bO7y2jW4n8S.m.Es6vjYpz7x8XBhZxBvckcr.VoC5cvqq') // pwdSucks!0
                ->setEmail($this->faker->email)
                ->setRoles([Member::ROLE_USER]);

            if ($i <= 3) {
                if ($i === 0 || $i === 1) {
                    $member->setEmail("moderator@snow.com");
                    $member->addRole(Member::ROLE_MODERATOR);
                }

                if ($i === 0 || $i === 2) {
                    $member->setEmail("editor@snow.com");
                    $member->addRole(Member::ROLE_EDITOR);
                }
                if ($i === 0 || $i === 3) {
                    $member->setEmail("admin@snow.com");
                    $member->addRole(Member::ROLE_ADMIN);
                }

                if ($i === 0) {
                    $member->setEmail("god@snow.com");
                }
            }

            // Picture
            if (mt_rand(0, 2)) {
                $picture = $this->newImage(500, 300);
                $member->setPicture($picture);
                $picture->setMember($member);
                $manager->persist($picture);
            }
            $manager->persist($member);
            $members[] = $member;
        }

        // Trick groups
        for ($i = 1; $i <= $numberOfTrickGroups; $i++) {
            $description = "<p>" . implode("</p><p>", $this->faker->paragraphs(mt_rand(1, 2))) . "</p>";
            $trickGroup = new TrickGroup();
            $trickGroup->setName("Groupe n°$i")
                ->setDescription("<h2>Description du groupe n°$i</h2>$description");
            $manager->persist($trickGroup);
            $trickGroups[] = $trickGroup;
        }

        // Tricks
        for ($i = 1; $i <= $numberOfTricks; $i++) {
            $description = $this->generateParagraphs(3, 5);
            $imageWidth = 1024;
            $imageHeight = 768;

            $trick = new Trick();
            $trick->setName("trick $i")
                ->setDescription("<h2>description $i</h2>$description")
                ->setCreatedAt($this->faker->dateTimeThisYear())
                ->setMainImage($this->faker->imageUrl($imageWidth, $imageHeight));

            // Images
            for ($j = 0, $size = rand(0, 10); $j < $size; $j++) {
                $image = self::newImage($imageWidth, $imageHeight);
                $trick->addImage($image);
                $image->setTrick($trick);
                $manager->persist($image);
            }

            // Author
            $trick->setAuthor($members[
                mt_rand(0, $numberOfMembers - 1)
            ]);

            // Comments
            for ($j = 0, $size = rand(0, 20); $j < $size; $j++) {
                $comment = new Comment();
                $comment->setAuthor($members[
                    mt_rand(0, $numberOfMembers - 1)
                ])
                    ->setCreatedAt($this->faker->dateTimeThisYear())
                    ->setTrick($trick)
                    ->setContent(strip_tags($this->generateParagraphs(1, 3)));
                $trick->addComment($comment);
                $manager->persist($comment);
            }

            $tricks[] = $trick;
        }

        // Videos
        $videoUrls = file(dirname(dirname(__DIR__)) . "/demodata/youtube_videos");
        $numberOfVideos = count($videoUrls);
        $k = 0;

        foreach ($videoUrls as $videoUrl) {
            $video = new Video();
            $video->setUrl($videoUrl);
            $videos[] = $video;
        }

        for ($i = 0; $i < $numberOfTricks; $i++) {
            $addVideos = rand(0, 3);
            if ($addVideos && $k < $numberOfVideos) {
                for ($j = 0, $size = mt_rand(0, 5); $j < $size; $j++) {
                    $tricks[$i]->addVideo($videos[$k]);
                    $videos[$k]->setTrick($tricks[$i]);
                    $manager->persist($videos[$k]);
                    $k++;
                    if ($k >= $numberOfVideos) {
                        break;
                    }
                }
            }
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
}
