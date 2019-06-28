<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ImageRepository")
 */
class Image
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Trick", inversedBy="images")
     */
    private $trick;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Member", inversedBy="picture")
     */
    private $member;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): self
    {
        $this->trick = $trick;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMember(): ?Member
    {
        return $this->member;
    }

    /**
     * @param Member $member
     * @return Image
     */
    public function setMember(?Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function __toString()
    {
        return $this->url;
    }
}
