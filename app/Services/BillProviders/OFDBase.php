<?php

namespace App\Services\BillProviders;

class OFDBase
{
    protected function sendRequest(string $url): object
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response);
    }

    protected function getHtml(string $url): string
    {
        return file_get_contents($url);
    }

    protected function amountFromStr(string $str): float
    {
        return (int)filter_var(trim($str), FILTER_SANITIZE_NUMBER_FLOAT) / 100;
    }
}
