<?php
declare(strict_types=1);

namespace App\Console;

use App\Services\ItemService;
use App\Storage\Type\File;
use Goutte\Client as GoutteClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class IpAustraliaCommand extends Command
{
    protected function configure()
    {
        $this->setName('au-gov')
            ->setDescription('Scrap info from https://search.ipaustralia.gov.au')
            ->addArgument('word', InputArgument::REQUIRED, 'scrap info by word (trade mark name)');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $word = $input->getArgument('word');
        $output->writeln("Start scrap info by word: {$word}");
        $start = microtime(true);

        $config = config('config');
        $doSearchHeaders = config('dosearch_headers', 'ipaustralia');
        $doSearchBodyParams = config('dosearch_body', 'ipaustralia');

        $doSearchBodyParams['wv[0]'] = $word;
        $doSearchHeaders['Content-Length'] = strlen(http_build_query($doSearchBodyParams));

        $response = (new GuzzleClient())->post(
            $config['ipaustralia_doserach_url'],
            [
                RequestOptions::HEADERS => $doSearchHeaders,
                RequestOptions::FORM_PARAMS => $doSearchBodyParams,
                RequestOptions::CONNECT_TIMEOUT => 10,
                RequestOptions::ALLOW_REDIRECTS => false
            ],
        );

        $redirectLocation = $response->getHeader('Location')[0];

        if (!$redirectLocation) {
            $output->writeln("<error>Location header not found, return</error>");
            return 0;
        }

        $fileStorage = new File();
        $goutteClient = new GoutteClient();
        $itemService = new ItemService();
        $filePath = storage_path(date('Y-m-d') . '_' . $word . '_crawler');
        $data = [];
        $page = 0;
        $totalCount = 0;
        while (true) {
            $output->writeln("<comment>Start search, page {$page}</comment>");

            $htmlCrawler = $goutteClient->request('GET', $redirectLocation . '&p=' . $page);
            $countItems = $htmlCrawler->filter('tbody > tr')->count();

            if (!$countItems && $page === 0) {
                $output->writeln("<error>Nothing found by word {$word}, return</error>");
                return 0;
            }

            //find by "word" available only first 20 pages | 2000 records
            if (!$countItems || $page === 20) {
                $fileStorage->save(
                    $filePath,
                    json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
                    File::JSON_EXTEND
                );
                $output->writeln("<error>Not found, page {$page}, break</error>");
                break;
            }

            $htmlCrawler->filter('tbody > tr')->each(function (Crawler $item) use (
                &$data,
                &$totalCount,
                $itemService
            ) {
                ++$totalCount;
                return $data[] = $itemService->handle($item);
            });

            $output->writeln("<comment>Page {$page} completed</comment>");
            ++$page;
        }

        $scriptTime = round(microtime(true) - $start, 1);
        $output->writeln("<info>End. File path: {$filePath}</info>");
        $output->writeln("<info>Total count: {$totalCount}</info>");
        $output->writeln("<info>Script execution time {$scriptTime} secs</info>>");

        return 1;
    }
}
