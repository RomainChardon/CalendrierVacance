<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @ORM\ManyToMany(targetEntity=Vacances::class, inversedBy="users", cascade={"persist", "remove"})
     */
    private $Vacances;

    /**
     * @ORM\ManyToOne(targetEntity=Groupe::class, inversedBy="users")
     */
    private $Groupe;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $nbConges;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mail;

    /**
     * @ORM\Column(type="boolean")
     */
    private $desactiver;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $cadre;

    /**
     * @ORM\OneToMany(targetEntity=Vacances::class, mappedBy="User")
     */
    private $vacances;

    /**
     * @ORM\ManyToOne(targetEntity=Groupe::class, inversedBy="User")
     */
    private $groupe;

    public function __construct()
    {
        $this->Vacances = new ArrayCollection();
        $this->vacances = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * @return Collection|Vacances[]
     */
    public function getVacances(): Collection
    {
        return $this->Vacances;
    }

    public function addVacance(Vacances $vacance): self
    {
        if (!$this->Vacances->contains($vacance)) {
            $this->Vacances[] = $vacance;
        }

        return $this;
    }

    public function removeVacance(Vacances $vacance): self
    {
        $this->Vacances->removeElement($vacance);

        return $this;
    }

    public function getGroupe(): ?Groupe
    {
        return $this->Groupe;
    }

    public function setGroupe(?Groupe $Groupe): self
    {
        $this->Groupe = $Groupe;

        return $this;
    }

    public function getNbConges(): ?float
    {
        return $this->nbConges;
    }

    public function setNbConges(?float $nbConges): self
    {
        $this->nbConges = $nbConges;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getDesactiver(): ?bool
    {
        return $this->desactiver;
    }

    public function setDesactiver(bool $desactiver): self
    {
        $this->desactiver = $desactiver;

        return $this;
    }

    public function getCadre(): ?bool
    {
        return $this->cadre;
    }

    public function setCadre(?bool $cadre): self
    {
        $this->cadre = $cadre;

        return $this;
    }
}
