<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mime\Email;
use App\Service\ICSGenerator;
use Symfony\Component\Mailer\MailerInterface;
use App\Repository\UserRepository;



class MailSubscriber implements EventSubscriberInterface
{
    public MailerInterface $mailer;

    public function __construct(MailerInterface $mailer){
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
            'mail.event' => "mailTest",
        ];
    }

    public function mailTest($event,$ics,$dateDebut,$dateFin,$utilisateur){



        $email = (new Email())
        ->from('enzo.mangiante.adeo@gmail.com')
        ->to($utilisateur->getMail())
        ->subject("Confirmation d'enregistrement de vos congés")
        ->html("<p> Vos Vacances du $dateDebut au $dateFin sont enregistrées par la direction. </p>")
        ->attachFromPath($ics, null, 'text/calendar');
    }

    public function logException(ExceptionEvent $event)
    {
        // ...
    }

    public function notifyException(ExceptionEvent $event)
    {
        // ...
    }
}