<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int user_id
 * @property int xpoint
 * @property int ypoint
 * @property int total_like
 */
class User extends Authenticatable
{
    use Notifiable, Rankable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $primaryKey = 'user_id';

    protected $appends = [
        'rank',
        'clan_rank',
    ];

    public $timestamps = false;

    public function getPointAttribute()
    {
        return $this->xpoint + $this->ypoint;
    }

    public function getRankableIndexName(): string
    {
        return $this->getTable();
    }

    public function rankableAttributes(): array
    {
        return [
            'point' => 10,
            'total_like' => 10
        ];
    }

    public function getRankableGroups(): array
    {
        return [
            'clan_id'
        ];
    }

    public function getClanRankAttribute()
    {
        return $this->getGroupRank('clan_id');
    }
}
