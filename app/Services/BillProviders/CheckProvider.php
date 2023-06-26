<?php

namespace App\Services\BillProviders;

use Carbon\Carbon;

class CheckProvider extends OFDBase implements IBillProvider
{

    protected string $url;
    protected string $rawData;

    protected array $months = [
        'янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек',
    ];

    public function getData(string $url): array
    {
        $this->url = $this->urlTransform($url);
        $this->rawData = $this->getHtml($this->url);
        return $this->parseData();
    }

    function urlTransform(string $url): string
    {
        # https://4ek.kz/?o=transtelecom&i=834491435710&f=010101834255&s=40000.0&t=20220610T165650
        # to
        # https://ttk.4ek.kz/t/?i=834491435710&f=010101834255&s=40000.0&t=20220610T165650

        list('query' => $query) = parse_url($url);
        return "https://ttk.4ek.kz/t/?$query";
    }

    function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    function parseData(): array
    {
        $html = $this->get_string_between($this->rawData, '<main role="main">', '</main>');
        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        $finder = new \DOMXPath($dom);
        $header = $finder->query("//div[@class='ticket_header']")[0];
        $datetime = '';
        $company = '';
        $billNumber = '';
        foreach ($header->getElementsByTagName("div") as $headerLineIdx => $headerLine) {
            switch ($headerLineIdx) {
                case 0:
                    $company = $headerLine->textContent;
                    break;
                case 5:
                    $dateText = $headerLine->textContent;
                    $dateParts = preg_split("/[:\,]/", $dateText);
                    $dateParts = array_map('trim', $dateParts);
                    list(1 => $date, 2 => $hours, 3 => $minutes) = $dateParts;
                    list(0 => $day, 1 => $month, 2 => $year) = explode(' ', $date);
                    $month = array_search($month, $this->months) + 1;
                    $datetime = Carbon::parse("$year-$month-$day $hours:$minutes");
                    break;
            }
        }

        $body = $finder->query("//ol[@class='ready_ticket__items_list']")[0];
        $positionItems = $body->getElementsByTagName("li");
        $items = [];
        foreach ($positionItems as $positionItem) {
            $texts = [];
            foreach ($positionItem->childNodes as $childNode) {
                $text = trim($childNode->textContent);
                if ($text) {
                    $texts[] = $text;
                }
            }
            if (!isset($texts[1])) {
                if (str_contains($texts[0], 'Коррекция округления')) {
                    $discountString = trim($texts[0]);
                    list($discount, $fee) = explode("-", $discountString);
                    list($name,$price) = explode("  ",trim($discount));
                    $item = [
                        'name' => $name,
                        'quantity' => 1,
                        'price' => $this->amountFromStr($price) * -1,
                        'amount' => $this->amountFromStr($price) * -1,
                    ];
                    $items[] = $item;
                }
                continue;
            }
            list($itemName, $amountData) = $texts;
            $chars = preg_split('/[x,шт,=]/', $amountData);

            $amountDataValues = array_values(array_filter(array_map('trim', $chars)));
            list($price, $quantity, $amount) = $amountDataValues;
            $priceParts = explode("\n", $price);
            $price = $priceParts[count($priceParts) - 1];
            $item = [
                'name' => $itemName,
                'quantity' => (float)$quantity,
                'price' => $this->amountFromStr($price),
                'amount' => $this->amountFromStr($amount),
            ];
            $items[] = $item;
        }

        $footer = $finder->query("//div[@class='ticket_footer']")[0];
        $footerLines = $footer->getElementsByTagName("div");
        foreach ($footerLines as $footerLine) {
            $text = trim($footerLine->textContent);
            if (str_contains($text, 'Фискальный признак')) {
                $billNumber = trim(str_replace('Фискальный признак:', '', $text));
            }
        }

        return [
            'billNumber' => $billNumber,
            'company' => trim($company),
            'date' => $datetime->format("Y-m-d H:i:s"),
            'items' => array_values($items),
        ];
    }
}
