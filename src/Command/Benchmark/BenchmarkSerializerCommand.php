<?php

namespace App\Command\Benchmark;

use App\Entity\Page\Page;
use App\Entity\User\User;
use App\Repository\PageRepository;
use App\Service\Helper\DefaultNormalizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BenchmarkSerializerCommand extends Command
{
    public function __construct(
        private DefaultNormalizer $normalizer,
        private PageRepository $pageRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('benchmark:serializer')
            ->setDescription('Benchmarks the Symfony vs custom serializer.')
            ->addArgument('pageId', mode: InputArgument::REQUIRED, description: 'The page ID you want to fetch and serialise; must exist')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        /** @var ?Page */
        $page = $this->pageRepository->findOneBy([], ['id' => 'DESC']);

        if (null === $page) {
            $style->error('No pages found in DB to benchmark.');

            return Command::FAILURE;
        }

        $user = $page->getUser() ?? $page->getProject()->getOwner();
        $this->benchmark($style, $user, $page);

        return Command::SUCCESS;
    }

    private function benchmark(SymfonyStyle $style, User $user, $object): void
    {
        $iterations = 1000;
        $style->text(\sprintf('%d iterations of serializing the same object.', $iterations));

        $start = microtime(true);

        foreach (range(1, $iterations) as $i) {
            $this->normalizer->normalize($user, $object, maxDepth: 999); // maxDepth is set to 999 to avoid any depth differences
        }

        $end = microtime(true);
        $customSerializerDuration = $end - $start;

        $style->text(sprintf('Custom serializer: %.2f seconds (%.2f ms avg)', $customSerializerDuration, $customSerializerDuration / $iterations * 1000));

        $start = microtime(true);

        foreach (range(1, $iterations) as $i) {
            $this->normalizer->symfonyNormalize($object);
        }

        $end = microtime(true);
        $symfonySerializerDuration = $end - $start;

        $style->text(sprintf('Symfony serializer: %.2f seconds (%.2f ms avg)', $symfonySerializerDuration, $symfonySerializerDuration / $iterations * 1000));

        $performanceDifference = $symfonySerializerDuration / $customSerializerDuration * 100;

        $style->success(sprintf('Customer serializer is %.2f%% %s than the Symfony serializer.', $performanceDifference, $performanceDifference > 0 ? 'faster' : 'slower'));
    }
}