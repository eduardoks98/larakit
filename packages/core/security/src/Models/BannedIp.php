<?php

namespace Eduardoks98\Security\Models;

use Illuminate\Database\Eloquent\Model;

class BannedIp extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'banned_ips';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ip_address',
        'reason',
        'user_agent',
        'country',
        'city',
        'latitude',
        'longitude',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
