<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Hotel extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'location',
        'stars',
        'description',
        'status',
        'manager_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['name', 'location', 'stars', 'status']);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

}
