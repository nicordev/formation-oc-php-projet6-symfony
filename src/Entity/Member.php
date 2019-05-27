<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MemberRepository")
 * @UniqueEntity(
 *     fields={"email"},
 *     message="Cet email est déjà pris"
 * )
 * @UniqueEntity(
 *     fields={"name"},
 *     message="Ce nom est déjà pris"
 * )
 */
class Member implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Vous devez entrer un nom")
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Vous devez entrer un email")
     * @Assert\Email(message="Vous devez entrer un email valide")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Vous devez entrer un mot de passe")
     */
    private $password;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Image", mappedBy="member")
     */
    private $picture;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="author")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Trick", mappedBy="author")
     */
    private $tricks;

    /**
     * @ORM\Column(type="simple_array")
     */
    private $roles;

    public const ROLE_USER = "ROLE_USER";
    public const ROLE_MODERATOR = "ROLE_MODERATOR";
    public const ROLE_EDITOR = "ROLE_EDITOR";
    public const ROLE_ADMIN = "ROLE_ADMIN";

    public const ROLES = [
        self::ROLE_USER,
        self::ROLE_MODERATOR,
        self::ROLE_EDITOR,
        self::ROLE_ADMIN
    ];

    public const DEFAULT_PICTURE_URL = "/img/default_member.png";

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->tricks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPicture(): ?Image
    {
        return $this->picture;
    }

    public function setPicture(?Image $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    // Comments

    /**
     * Check if the Member is the author of a comment
     *
     * @param Comment $comment
     * @return bool
     */
    public function isCommentAuthor(Comment $comment)
    {
        return $comment->getAuthor()->getId() === $this->getId();
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

    // Tricks

    /**
     * Check if the Member is the author of a trick
     *
     * @param Trick $trick
     * @return bool
     */
    public function isAuthor(Trick $trick)
    {
        return $trick->getAuthor()->getId() === $this->getId();
    }

    /**
     * @return Collection|Trick[]
     */
    public function getTricks(): Collection
    {
        return $this->tricks;
    }

    public function addTrick(Trick $trick): self
    {
        if (!$this->tricks->contains($trick)) {
            $this->tricks[] = $trick;
            $trick->setAuthor($this);
        }

        return $this;
    }

    public function removeTrick(Trick $trick): self
    {
        if ($this->tricks->contains($trick)) {
            $this->tricks->removeElement($trick);
            // set the owning side to null (unless already changed)
            if ($trick->getAuthor() === $this) {
                $trick->setAuthor(null);
            }
        }

        return $this;
    }

    // UserInterface

    public function getUsername()
    {
        return $this->getName();
    }

    public function eraseCredentials()
    {
        
    }

    public function getSalt()
    {
        
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    // Roles

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role)
    {
        $this->roles[] = $role;
    }
}
