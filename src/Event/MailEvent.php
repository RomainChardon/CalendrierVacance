<?php

namespace App\Event;

use App\Entity\User;
use DateTimeImmutable;
use Symfony\Contracts\EventDispatcher\Event;

class MailEvent extends Event
{
    protected DateTimeImmutable $dateDebut;
    protected DateTimeImmutable $dateFin;
    protected User $user;
    protected String $ics;

    public function __construct(DateTimeImmutable $dateDebut, DateTimeImmutable $dateFin, User $user, string $ics)
    {
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->user = $user;
        $this->ics = $ics;
    }

    public function getDateDebut()
    {
        return $this->dateDebut->format('d/m/Y');
    }

    public function getDateFin()
    {
        return $this->dateFin->format('d/m/Y');
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getICS()
    {
        return $this->ics;
    }
}
