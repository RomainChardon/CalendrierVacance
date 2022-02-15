<?php

namespace App\Command;

use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddCongesCommand extends Command
{
    private $userRepository;
    private $em;
    protected static $defaultName = 'app:add-conges';

    public function __construct(UserRepository $userRepository, EntityManagerInterface $em)
    {
        parent::__construct(null);
        $this->userRepository = $userRepository;
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ajd = new DateTime('now');
        $io = new SymfonyStyle($input, $output);
        $users = $this->userRepository->findAll();
        $io->progressStart(count($users));
        foreach ($users as $user) {
            $nbConges = $user->getNbConges();
            $nbConges += 2.08;
            $user->setNbConges($nbConges);
            $io->progressAdvance();
            $this->em->persist($user);
            $this->em->flush();
            $fp = fopen("src\Command\logs.txt", 'a+');
            fwrite($fp, 'Ajout de 2.3 j de congés a '.$user->getUsername().' le '.$ajd->format('Y-m-d')."\n");
            fclose($fp);
        }
        $io->progressFinish();
        $io->success('Les congés ont étaient rajoutées');

        return 0;
    }
}
