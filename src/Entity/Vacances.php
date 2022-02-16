<?php

namespace App\Entity;

use App\Repository\VacancesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VacancesRepository::class)
 */
class Vacances
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date_immutable")
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="date_immutable")
     */
    private $dateFin;

    /**
     * @ORM\Column(type="boolean")
     */
    private $autoriser;

    /**
     * @ORM\Column(type="boolean")
     */
    private $attente;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $maladie;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $demiJournee;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $sansSoldes;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $RTT;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $annuler;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateDemande;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateAnnulation;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $textAnnuler;

    /**
     * @ORM\ManyToOne(targetEntity="User",cascade={"persist"},inversedBy="vacances")
     */
    private $User;

    public function __construct()
    {
        $this->utilisateurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getAutoriser(): ?bool
    {
        return $this->autoriser;
    }

    public function setAutoriser(bool $autoriser): self
    {
        $this->autoriser = $autoriser;

        return $this;
    }

    public function getAttente(): ?bool
    {
        return $this->attente;
    }

    public function setAttente(bool $attente): self
    {
        $this->attente = $attente;

        return $this;
    }

    public function getMaladie(): ?bool
    {
        return $this->maladie;
    }

    public function setMaladie(bool $maladie): self
    {
        $this->maladie = $maladie;

        return $this;
    }

    public function getDemiJournee(): ?string
    {
        return $this->demiJournee;
    }

    public function setDemiJournee(?string $demiJournee): self
    {
        $this->demiJournee = $demiJournee;

        return $this;
    }

    public function getSansSoldes(): ?bool
    {
        return $this->sansSoldes;
    }

    public function setSansSoldes(?bool $sansSoldes): self
    {
        $this->sansSoldes = $sansSoldes;

        return $this;
    }

    public function getRTT(): ?bool
    {
        return $this->RTT;
    }

    public function setRTT(bool $RTT): self
    {
        $this->RTT = $RTT;

        return $this;
    }

    public function getAnnuler(): ?bool
    {
        return $this->annuler;
    }

    public function setAnnuler(?bool $annuler): self
    {
        $this->annuler = $annuler;

        return $this;
    }

    public function getDateDemande(): ?\DateTimeInterface
    {
        return $this->dateDemande;
    }

    public function setDateDemande(?\DateTimeInterface $dateDemande): self
    {
        $this->dateDemande = $dateDemande;

        return $this;
    }

    public function getDateAnnulation(): ?\DateTimeInterface
    {
        return $this->dateAnnulation;
    }

    public function setDateAnnulation(?\DateTimeInterface $dateAnnulation): self
    {
        $this->dateAnnulation = $dateAnnulation;

        return $this;
    }

    public function getTextAnnuler(): ?string
    {
        return $this->textAnnuler;
    }

    public function setTextAnnuler(?string $textAnnuler): self
    {
        $this->textAnnuler = $textAnnuler;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): self
    {
        $this->User = $User;

        return $this;
    }
}
