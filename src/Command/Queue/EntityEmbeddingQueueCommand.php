<?php

namespace App\Command\Queue;

use App\Service\Helper\Debug;
use App\Service\Search\EntityEmbeddingQueueService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EntityEmbeddingQueueCommand extends Command
{
    public const RUNNING_TIME = 55;

    public function __construct(
        private EntityEmbeddingQueueService $entityEmbeddingQueueService,
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('queue:entity-embedding')
            ->setDescription('Process the entity embedding queue')
            ->addOption('debug', mode: InputOption::VALUE_NONE, description: 'Enable debug mode')
            ->addOption('daemon', mode: InputOption::VALUE_NONE, description: 'Run the command as a daemon')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $isDaemon = $input->getOption('daemon');

        if ($input->getOption('debug')) {
            Debug::enable();
        }

        if ($isDaemon) {
            Debug::print('Running as daemon... (never stopping)');
        }

        $startTime = \time();
        $isIdling = false; // flag to indicate if we are idling; this is useful to know on the console output

        while (true) {
            if ($startTime + self::RUNNING_TIME < \time() && !$isDaemon) {
                Debug::print('Time is up! Exiting...');
                break;
            }

            $queueItems = $this->entityEmbeddingQueueService->getQueueItemsToProcess(limit: 1);

            if (\count($queueItems) === 0) {
                if (!$isIdling) {
                    Debug::print('Idling...');
                    $isIdling = true;
                }
            } else {
                $isIdling = false;
                Debug::print(\sprintf('Found %d queue items to process...', \count($queueItems)));

                foreach ($queueItems as $queueItem) {
                    $this->entityEmbeddingQueueService->processQueueItem($queueItem);
                }
    
                // make sure all changes and removals are pushed to the database
                $this->em->flush();
                $this->em->clear();
            }

            // always wait 0.5 seconds before processing the next batch; this saves us CPU cycles
            \usleep(500000);
        }

        return Command::SUCCESS;
    }
}