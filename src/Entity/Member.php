<?php

namespace App\Entity;

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

    public const ROLE_USER = "ROLE_USER";
    public const ROLE_MODERATOR = "ROLE_MODERATOR";
    public const ROLE_EDITOR = "ROLE_EDITOR";
    public const ROLE_ADMIN = "ROLE_ADMIN";

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

    public function getRoles()
    {
        return ['ROLE_USER'];
    }
}
