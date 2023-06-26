<?php

namespace App\Services\BillProviders;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\ResponseInterface;

class OOFD extends OFDBase implements IBillProvider
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
        # http://consumer.oofd.kz/?i=1319093233&f=620200165500&s=33804.0&t=20220321T160230
        # through
        # https://consumer.oofd.kz/ticket/9c745a52-5f7c-4faf-ae3e-a071e3a83f42
        # to
        # https://consumer.oofd.kz/api/tickets/ticket/9c745a52-5f7c-4faf-ae3e-a071e3a83f42
        $url = str_replace('http://', 'https://', $url);
        $path = $this->askReturnLocations($url);
        return "https://consumer.oofd.kz/api/tickets$path";
    }

    public function askReturnLocations($url)
    {
        $client = new Client(['verify' => false ]);
        $client->request('get',$url,[
            'on_stats' => function (TransferStats $stats) use (&$finalUrl) {
                $finalUrl = $stats->getEffectiveUri();
            }
        ]);
       return $finalUrl->getPath();
    }

    function parseData(): array
    {
        $data = $this->rawData;
        $company = $data->orgTitle;
        $ticket = $data->ticket;
        $rawItems = $ticket->items;
        $billNumber = $ticket->fiscalId;

        $items = array_filter(array_map(function ($item) {
            if (isset($item->commodity)) {
                $itemData = $item->commodity;
                return [
                    'name' => $itemData->name,
                    'quantity' => $itemData->quantity,
                    'price' => $itemData->price,
                    'amount' => $itemData->sum
                ];
            }
            if (isset($item->discount)) {
                $itemData = $item->discount;
                return [
                    'name' => $itemData->name,
                    'quantity' => 1,
                    'price' => $itemData->sum * -1,
                    'amount' => $itemData->sum * -1
                ];
            }
            return null;

        }, $rawItems));

        if (isset($ticket->discount)) {
            $discount = $ticket->discount;
            $items[] = [
                'name' => $discount->name,
                'quantity' => 1,
                'price' => $discount->sum * -1,
                'amount' => $discount->sum * -1
            ];
        }

        $date = Carbon::parse($ticket->transactionDate);

        return [
            'billNumber' => $billNumber,
            'company' => $company,
            'date' => $date->format("Y-m-d H:i:s"),
            'items' => $items,
        ];
    }
}
