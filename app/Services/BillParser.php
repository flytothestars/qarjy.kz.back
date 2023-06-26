<?php

namespace App\Services;

use App\Services\BillProviders\CheckProvider;
use App\Services\BillProviders\IBillProvider;
use App\Services\BillProviders\KOFD;
use App\Services\BillProviders\OOFD;

class BillParser
{
    protected string $url;
    protected array $data;
    protected IBillProvider $provider;

    public function loadBill(string $url): void
    {
        $this->detectProvider($url);
        $this->data = $this->provider->getData($url);
    }

    public function getData(): array
    {
        return $this->data;
    }

    protected function detectProvider(string $url): void
    {
        if (str_contains($url, 'consumer.oofd.kz')) {
            $this->provider = new OOFD();
        } elseif (str_contains($url, 'kofd.kz')) {
            $this->provider = new KOFD();
        } else if (str_contains($url, 'ofd1.kz') || str_contains($url, '4ek.kz')) {
            $this->provider = new CheckProvider();
        } else {
            throw new \Error('Unknown provider');
        }
    }

}
