## Introduction

A leaderboard demo. the `App\Rankable` trait can easily attach to any model with minimal configuration to gain ranking capability with Redis. 

## Configuration

To get started, You may consider using [Laravel Homestead](https://laravel.com/docs/homestead), since it is the easiest way to get everything required to start a Laravel project and of course it comes with Redis.

First, run the following command on your terminal to clone this repo

```
git clone https://github.com/siewwp/rankable
```

Next, run the following command to generate your `APP_KEY`

```
php artisan key:generate
```

Next, run the following command to migrate, seed (1000 users) your database and update ranking at redis

```
php artisan migrate:fresh --seed
```

That's it, your leaderboard is now ready

## Demo

Go to the following url to get the top 100 user  

```
{host}/users/top
```

Go to the following url to view individual user rank

```
{host}/users/{user_id}
```