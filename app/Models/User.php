<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['name', 'email', 'role', 'wallet_balance']);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'wallet_balance',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function hotels()
    {
        return $this->hasMany(Hotel::class, 'manager_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function addToWallet($amount, $method, $description = "")
    {
        $this->wallet_balance += $amount;
        $this->save();

        $this->transactions()->create([
            'type' => 'credit',
            'amount' => $amount,
            'method' => $method,
            'description' => $description,
        ]);
    }

    public function deductFromWallet($amount, $description = "")
    {
        $this->wallet_balance -= $amount;
        $this->save();

        $this->transactions()->create([
            'type' => 'debit',
            'amount' => $amount,
            'method' => 'wallet',
            'description' => $description,
        ]);
    }

}
