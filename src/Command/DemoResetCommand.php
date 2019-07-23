<?php

namespace App\Command;

use App\Entity\Meeting;
use App\Entity\Notification;
use App\Helper\DemoHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DemoResetCommand extends Command
{
    protected static $defaultName = 'demo:reset';

    /** @var EntityManagerInterface */
    private $em;

    /** @var DemoHelper */
    private $helper;

    public function __construct(EntityManagerInterface $em, DemoHelper $helper)
    {
        $this->em = $em;
        $this->helper = $helper;

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
            ++$deleted;
        }

        $output->writeln($deleted.' meetings deleted.');

        $notifications = $this->em->getRepository(Notification::class)->findAll();

        foreach ($notifications as $notification) {
            $this->em->remove($notification);
            ++$deleted;
        }
        $output->writeln('Notifications deleted.');

        $this->em->flush();

        // Create Demo System
        $this->helper->createDemoSystem();
    }
}
