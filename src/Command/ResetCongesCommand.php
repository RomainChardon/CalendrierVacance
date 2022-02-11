<?php
namespace App\Command;

use DateTime;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;



class ResetCongesCommand extends Command
{

    private $userRepository;
    private $em;
    protected static $defaultName = 'app:reset-conges';


    public function  __construct( UserRepository $userRepository, EntityManagerInterface $em){
        parent::__construct(null);
        $this->userRepository = $userRepository;
        $this->em = $em;
    }
    protected function configure(): void
    {
        // ...
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {   
        $io = new SymfonyStyle($input, $output);
        $users = $this->userRepository->findAll();
        $io->progressStart(count($users));
        foreach ($users as $user) {
            $nbConges = $user->getNbConges();
            $nbConges =  0;
            $user->setNbConges($nbConges);
            $io->progressAdvance();
            $this->em->persist($user);
            $this->em->flush();
        }
        $io->progressFinish();
        $io->success('Les congés ont étaient réinitialisées');

        return 0;
    }
}