<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Mobizon\MobizonApi;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'sms_code',
        'pin_code'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function incomeTransactions()
    {
        return $this->transactions()->where("type", 'income');
    }

    public function expenseTransactions()
    {
        return $this->transactions()->where("type", 'expense');
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function scopeManager($query)
    {
        return $query->whereIn("role", ["manager", "admin"]);
    }

    public function scopeCustomer($query)
    {
        return $query->where("role", 'customer');
    }

    public static function phoneToDigits(string $phone): string
    {
        return trim(preg_replace('/^1|\D/', "", $phone));
    }

    public function sendSMSCode($test = false): bool
    {
        if ($test) {
            $this->sms_code = 1423;
            return $this->save();
        }
        $this->sms_code = rand(1000, 9999);
        $this->save();

        $api = new MobizonApi(config('services.mobizon.secret'), config('services.mobizon.server'));
        $appName = config('app.name');
        #$alphaname = 'TEST';
        $sent = $api->call('message', 'sendSMSMessage', [
            'recipient' => $this->phone,
            'text' => "Код для входа в $appName: $this->sms_code",
            #'from' => $alphaname,
            //Optional, if you don't have registered alphaname, just skip this param and your message will be sent with our free common alphaname.
        ]);

        if ($sent) {
            return true;
        } else {
            return false;
        }
    }

    public function isManager(): bool
    {
        return $this->role == "manager" || $this->role == "admin";
    }

    public function updateBalance(float $decrease, float $increase)
    {
        $this->balance = $this->balance - $decrease + $increase;
        $this->save();
    }
}
