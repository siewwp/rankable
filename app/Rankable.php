<?php


namespace App;

use Illuminate\Support\Facades\Redis;

trait Rankable
{
    public static function bootRankableTrait()
    {
        static::saving(function ($model) {
            // todo, re-rank only if field that influence the ranking is dirty
            $model->updateRank();
        });

        static::deleted(function ($model) {
            $model->removePreviousIndexValue();
        });
    }

    public function getGroupIndexName($group)
    {
        return $this->getRankableIndexName() . ':' . $this->{$group};
    }

    public function getHashMapIndexName()
    {
        return $this->getRankableIndexName() . '.hash_map';
    }

    public function removePreviousIndexValue()
    {
        $key = $this->getKey();
        if (!$this->rankableUseLex()) {
            Redis::zrem($this->getRankableIndexName(), $key);

            foreach($this->getRankableGroups() as $group) {
                Redis::zrem($this->getGroupIndexName($group), $key);
            }
        } else {
            $previousIndexValue = Redis::hget($this->getHashMapIndexName(), $key);

            if (!$previousIndexValue) {
                return;
            }

            Redis::zrem($this->getRankableIndexName(), $previousIndexValue);

            foreach($this->getRankableGroups() as $group) {
                Redis::zrem($this->getGroupIndexName($group), $previousIndexValue);
            }
        }
    }

    public function updateRank()
    {
        $value = $this->getRankableValue();

        $this->removePreviousIndexValue();

        $key = $this->getKey();

        if (!$this->rankableUseLex()) {
            Redis::zadd($this->getRankableIndexName(), $value, $key);

            foreach($this->getRankableGroups() as $group) {
                Redis::zadd($this->getGroupIndexName($group), $value, $key);
            }
        } else {
            Redis::zadd($this->getRankableIndexName(), 0, $value);

            foreach($this->getRankableGroups() as $group) {
                Redis::zadd($this->getGroupIndexName($group), 0, $value);
            }

            Redis::hset($this->getHashMapIndexName(), 0, $value);
        }
    }

    public function rankableUseLex(): bool
    {
        return count($this->rankableAttributes()) > 1;
    }

    public function getRankableValue(): string
    {
        if (!$this->rankableUseLex()) {
            return (string) $this->{$this->rankableAttributes()[0]};
        }

        foreach ($this->rankableAttributes() as $attribute => $paddingSize) {
            $values[] = str_pad($this->{$attribute}, $paddingSize, 0, STR_PAD_LEFT);
        }

        $values[] = $this->getKey();

        return implode($values, ':');
    }

    public function getRankAttribute()
    {
        $score = $this->getRankableValue();
        return Redis::zrevrank($this->getRankableIndexName(), $score) + 1;
    }

    public function getGroupRank($group)
    {
        $score = $this->getRankableValue();
        return Redis::zrevrank($this->getGroupIndexName($group), $score) + 1;
    }

    public static function topRank($count)
    {
        $self = new static;

        $results = Redis::zrevrange($self->getRankableIndexName(), 0, $count);

        if ($self->rankableUseLex()) {
            $results = array_map(function ($key) {
                $parts = explode(':', $key);
                return end($parts);
            }, $results);
        }

        $keyName = $self->getKeyName();

        $concatResult = implode(',', $results);

        return static::whereIn($keyName, $results)
            ->orderByRaw("FIELD($keyName, $concatResult)")
            ->get();
    }

    public static function reloadRank()
    {
        Redis::flushall();

        foreach(static::all() as $model) {
            $model->updateRank();
        }
    }
}