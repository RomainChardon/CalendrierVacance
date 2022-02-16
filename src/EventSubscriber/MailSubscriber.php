<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailSubscriber implements EventSubscriberInterface
{
    public MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
            'mail.event' => 'mailTest',
            'mailAnnuler.event' => 'mailAnnuler',
            'mailICS.event' => 'mailTestICS',
        ];
    }

    public function mailTest($event)
    {
        $email = (new Email())
        ->from('enzo.mangiante.adeo@gmail.com')
        ->to($event->getUser()->getMail())
        ->subject("Confirmation d'enregistrement de vos congés")
        ->html('<p> Vos Vacances du '.$event->getDateDebut().' au '.$event->getDateFin().' sont enregistrées par la direction. </p>');
        $this->mailer->send($email);
    }

    public function mailAnnuler($event)
    {
        $email = (new Email())
        ->from('enzo.mangiante.adeo@gmail.com')
        ->to($event->getUser()->getMail())
        ->subject("Confirmation d'enregistrement de vos congés")
        ->html('<p> Votre demande d\'annulation des Vacances du '.$event->getDateDebut().' au '.$event->getDateFin().' a été enregistré par la direction. </p>');
        $this->mailer->send($email);
    }

    public function mailTestICS($event)
    {
        $email = (new Email())
        ->from('enzo.mangiante.adeo@gmail.com')
        ->to($event->getUser()->getMail())
        ->subject("Confirmation d'enregistrement de vos congés")
        ->html('<p> Vos Vacances du '.$event->getDateDebut().' au '.$event->getDateFin().' sont enregistrées par la direction. </p>')
        ->attachFromPath($event->getICS(), null, 'text/calendar');

        $this->mailer->send($email);
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
