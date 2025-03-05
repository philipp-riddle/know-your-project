<?php

namespace App\Command;

use App\Service\Integration\QdrantIntegration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetupCommand extends Command
{
    public function __construct(
        private QdrantIntegration $qdrant,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:setup')
            ->setDescription('Setup the application for development and testing.')
            // the full command description shown when running the command with the "--help" option
            ->setHelp('This command sets up the application for development and testing by creating a database and running migrations.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $style->title('Setting up the application');

        $style->section('Creating Qdrant collections');
        $collectionsToCreate = ['userData'];

        foreach ($collectionsToCreate as $collectionName) {
            $style->text("Creating collection $collectionName");

            try {
                $this->qdrant->createCollection($collectionName);
            } catch (\InvalidArgumentException $ex) {
                $style->text("Collection $collectionName already exists");
            } catch (\Exception $ex) {
                $style->text("Failed to create collection $collectionName: " . $ex->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}