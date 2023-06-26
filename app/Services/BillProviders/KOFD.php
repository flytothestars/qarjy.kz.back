<?php

namespace App\Services\BillProviders;

use Carbon\Carbon;

class KOFD extends OFDBase implements IBillProvider
{

    protected string $url;
    protected object $rawData;

    public function getData(string $url): array
    {
        $this->url = $this->urlTransform($url);
        $this->rawData = $this->sendRequest($this->url);
        return $this->parseData();
    }

    function urlTransform(string $url): string
    {
        # https://cabinet.kofd.kz/consumer?i=716276207501&f=010101802487&s=5900.00&t=20220518T133320
        # to
        # https://cabinet.kofd.kz/api/tickets?registrationNumber=010101802487&ticketNumber=716276207501
        list('query' => $query) = parse_url($url);
        $pairs = array_map(function ($pair) {
            $kv = explode("=", $pair);
            return [
                'key' => $kv[0],
                'value' => $kv[1],
            ];
        }, explode("&", $query));
        # [ {key:'',value:''},{key:'',value:''}]
        $registrationNumber = 'f';
        $ticketNumber = 'i';
        foreach ($pairs as $pair) {
            if ($pair['key'] === "f") {
                $registrationNumber = $pair['value'];
            } elseif ($pair['key'] === 'i') {
                $ticketNumber = $pair['value'];
            }
        }
        return "https://cabinet.kofd.kz/api/tickets?registrationNumber=$registrationNumber&ticketNumber=$ticketNumber";
    }

    function parseData(): array
    {
        $data = $this->rawData->data->ticket;

        $company = trim($data[0]->text ?? "Название компании неизвестно");
        $billNumber = '';
        $date = Carbon::now();

        $begins = 0;
        $finish = 0;
        $amount = 0;
        $itemRows = [];
        foreach ($data as $i => $row) {
            if (!isset($row->text)) {
                continue;
            }
            $text = $row->text;
            if (str_contains($text, 'ВРЕМЯ')) {
                $timeStr = trim(str_replace('ВРЕМЯ:', '', $text));
                $date = Carbon::parse($timeStr);
            }

            if (str_contains($text, 'ФИСКАЛЬНЫЙ ПРИЗНАК')) {
                $billNumber = trim(str_replace('ФИСКАЛЬНЫЙ ПРИЗНАК:', '', $text));
            }

            if (str_contains($text, "***********")) {
                $begins = $i + 1;
            }

            if (str_contains($text, "-------------")) {
                $endCount = mb_strlen(trim(str_replace('-', '', $text)));
                if ($endCount === 0) {
                    $finish = $i;
                }
            }

            if ($begins && $begins < $i + 1 && !$finish) {
                $itemRows[] = trim($text);
            }

            if (str_contains($text, 'ИТОГО:')) {
                $amount = trim(str_replace('ИТОГО:', '', $text));
            }
        }

        $itemRows = array_values(array_filter($itemRows, function ($item) {
            return mb_strpos($item, "НДС") === false;
        }));

        $items = [];
        foreach ($itemRows as $k => $itemRow) {
            if ($k % 2 == 0) {
                $items[$k]['name'] = trim($itemRow);
            } else {
                $priceRow = trim($itemRow);
                $parsedPriceRow = explode('x', $priceRow);
                $quantity = (float)trim($parsedPriceRow[0]);
                if (!isset($parsedPriceRow[1])) {
                    continue;
                }
                $parsedAmounts = explode("=", $parsedPriceRow[1]);

                $items[$k - 1]['quantity'] = $quantity;
                $items[$k - 1]['price'] = $this->amountFromStr($parsedAmounts[0]);
                $items[$k - 1]['amount'] = $this->amountFromStr($parsedAmounts[1]);
            }
        }
        $items = array_filter($items, function ($item) {
            return isset($item['amount']);
        });
        if (count($items) === 0) {
            $items = [
                [
                    "name" => "Покупка из $company",
                    "price" => $this->amountFromStr($amount),
                    "quantity" => 1,
                    "amount" => $this->amountFromStr($amount),
                ]
            ];
        }
        return [
            'billNumber' => $billNumber,
            'company' => $company,
            'date' => $date->format("Y-m-d H:i:s"),
            'items' => array_values($items),
        ];
    }
}
