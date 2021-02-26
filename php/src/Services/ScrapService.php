<?php
declare(strict_types=1);

namespace App\Services;

use Symfony\Component\DomCrawler\Crawler;

class ScrapService
{
    /**
     * @param Crawler $item
     * @return array
     * @throws \Exception
     */
    public function handle(Crawler $item): array
    {
        $number = $this->processNumber($item);
        $name = $this->processTradeMarkName($item);
        $logoUrl = $this->processTradeMarkImage($item);
        $classes = $this->processClasses($item);
        $status = $this->processStatus($item);

        return [
            'number' => $number['number'],
            'logo_url' => $logoUrl,
            'name' => $name,
            'classes' => $classes,
            'status' => $status,
            'details_page_url' => config('config')['host_name'] . $number['details_page_url'],
        ];
    }

    /**
     * @param string $json
     * @return string|string[]
     */
    public function prettyJsonData(string $json)
    {
        return str_replace(['[', ']'], ['', ','], $json);
    }

    /**
     * @param Crawler $item
     * @return array
     */
    private function processNumber(Crawler $item): array
    {
        return [
            'details_page_url' => $item->filter('td.number > a')->attr('href'),
            'number' => $item->filter('td.number')->text()
        ];
    }

    /**
     * @param Crawler $item
     * @return string
     */
    private function processTradeMarkName(Crawler $item): string
    {
        return $item->filter('td.trademark.words')->text();
    }

    /**
     * @param Crawler $item
     * @return mixed|null
     */
    private function processTradeMarkImage(Crawler $item)
    {
        if ($item->filter('td.trademark.image')->count() !== 0) {
            $tradeMarkImage = $item->filter('td.trademark.image')->html();
            preg_match('/src="(.*?)"/i', $tradeMarkImage, $matches);

            return $matches[1] ?? null;
        }

        return null;
    }

    /**
     * @param Crawler $item
     * @return string
     */
    private function processClasses(Crawler $item): string
    {
        return $item->filter('td.classes')->text();
    }

    /**
     * @param Crawler $item
     * @return string
     */
    private function processStatus(Crawler $item): string
    {
        return preg_replace('/[^\x{20}-\x{7F}]/u', '', $item->filter('td.status')->text());
    }
}
