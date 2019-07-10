<?php

namespace App\Command;

use App\Entity\Meeting;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DemoResetCommand extends Command
{
    protected static $defaultName = 'demo:reset';

    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Removes all existing meetings and resets demo system');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $meetings = $this->em->getRepository(Meeting::class)->findAll();

        $deleted = 0;
        foreach ($meetings as $meeting) {
            $this->em->remove($meeting);
            $deleted++;
        }

        $this->em->flush();
        $output->writeln($deleted.' meetings deleted.');
    }
}
