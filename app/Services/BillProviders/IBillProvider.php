<?php

namespace App\Services\BillProviders;
interface IBillProvider
{
    function urlTransform(string $url): string;

    function parseData(): array;

    public function getData(string $url): array;
}
