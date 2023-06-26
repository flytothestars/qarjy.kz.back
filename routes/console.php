<?php

use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('bill', function () {
    #$url = "https://cabinet.kofd.kz/consumer?i=461754361&f=010101802487&s=1960.00&t=20220603T205716";
    #$url = "https://cabinet.kofd.kz/consumer?i=716276207501&f=010101802487&s=5900.00&t=20220518T133320";

    #$url = "https://consumer.oofd.kz/ticket/9c745a52-5f7c-4faf-ae3e-a071e3a83f42";
    #$url = "https://consumer.oofd.kz/ticket/2890daf4-86c4-4aef-aea9-ce39ccf2af18";
    #$url = "https://consumer.oofd.kz/ticket/74e5a1af-e958-4ac5-ac7f-2ad772635039";
    #   $url = "https://consumer.oofd.kz/ticket/00e1568b-6ee4-4db0-b9a8-9efe2351c653";
    #$url = "https://4ek.kz/?o=transtelecom&i=834491435710&f=010101834255&s=40000.0&t=20220610T165650";
    #$url = "https://consumer.oofd.kz/ticket/11e35700-5713-4e6b-a7cd-567f39386dd9";
    #$url = "http://consumer.oofd.kz/?i=1319093233&f=620200165500&s=33804.0&t=20220321T160231";

    #$url = "https://ofd1.kz/t/?i=362397639340&f=010101907421&s=1820.0&t=20220723T180527";
    $url = "https://ofd1.kz/t/?i=362397639340&f=010101907421&s=1820.0&t=20220723T180527";
    #$url = "http://consumer.oofd.kz?i=3041387980&f=010100487302&s=2400.0&t=20220722T161532";
    #$url = "http://consumer.kofd.kz?i=717148297551&f=010101869173&s=10000.00&t=20220528T154623";
    #$url = "https://cabinet.kofd.kz/consumer?i=720163788689&f=010101017498&s=474.00&t=20220702T133932";
    # $url = "https://ofd1.kz/t/?i=4063540167&f=010101907421&s=1651.0&t=20220803T202419";
    # $url = "http://consumer.oofd.kz/?i=3556939711&f=620300134986&s=6400.0&t=20220808T152352";
    #$url = "http://consumer.oofd.kz?i=417397048&f=010101641481&s=414.00&t=20220810T132707";
    #$url = "https://consumer.oofd.kz/?i=900617631&f=010100768757&s=40950.0&t=20220808T161324";
    #$url='https://ofd1.kz/t/?i=726647772805&f=010101907421&s=1503.0&t=20220820T182353';
    $parser = new \App\Services\BillParser();
    $parser->loadBill($url);
    #dd(Carbon::parse($parser->getData()['date'])->format("H:i d.m.Y"));
    dd($parser->getData());
});

Artisan::command('chrome', function () {
    $str = "[\"1.\\n\u0422\u0420\u0423\u0421\u042b\\n1\\n9\u00a0990,00\u00a0\u20b8\\n1\\n9\u00a0990,00\u00a0\u20b8\",\"2.\\n\u041d\u041e\u0421\u041a\u0418\\n1\\n7\u00a0590,00\u00a0\u20b8\\n1\\n7\u00a0590,00\u00a0\u20b8\"]";
    $items = array_map(function ($item) {
        list($idx, $title, $categoryId, $price, $quantity, $amount) = explode("\n", $item);
        return [
            'title' => $title,
            'price' => (int)filter_var($price, FILTER_SANITIZE_NUMBER_INT) / 100,
            'quantity' => (int)$quantity,
            'amount' => (int)filter_var($amount, FILTER_SANITIZE_NUMBER_INT) / 100
        ];
    }, json_decode($str));
    // dd($parse);

});
Artisan::command('text', function () {
    $tag = "АИ-95";
    $title = "ТРК 17:АИ-95";
    var_dump(mb_stripos($title, $tag));
});
Artisan::command('distribute', function () {
    $transactions = \App\Models\Transaction::query()->expense()->get()->each(function (\App\Models\Transaction $transaction) {
        $transaction->distribute();
    });
});
