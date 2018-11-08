<?php

namespace App\Models\Traits;

use App\Models\Topic;
use App\Models\Reply;
use Carbon\Carbon;

trait ActiveUserHelper
{
    protected $topic_weight = 4;
    protected $reply_weight = 1;
    protected $pass_day = 7;
    protected $user_number = 6;

    protected $users = [];

    protected $cache_key = 'larabbs4_active_users';
    protected $cache_expire_in_minites = 65;

    public function getActiveUsers()
    {
        return \Cache::remember($this->cache_key, $this->cache_expire_in_minites, function () {
            return $this->calculateActiveUsers();
        });
    }

    public function calculateAndCacheActiveUsers()
    {
        $active_users = $this->calculateActiveUsers();
        $this->cacheActiveUsers($active_users);
    }

    private function calculateActiveUsers()
    {
        $this->calculateTopicScore();
        $this->calculateReplyScore();

        $users = array_sort($this->users, function ($user) {
            return $user['score'];
        });
        $users = array_reverse($users, true);
        $users = array_slice($users, 0, $this->user_number, true);

        $active_users = collect();
        foreach ($users as $id => $user) {
            if ($user = $this->find($id)) {
                $active_users->push($user);
            }
        }

        return $active_users;
    }

    private function calculateTopicScore()
    {
        $topic_users = Topic::query()->select(\DB::raw('user_id, count(*) as topic_count'))
                                    ->where('created_at', '>=', Carbon::now()->subDays($this->pass_day))
                                    ->groupBy('user_id')
                                    ->get();

        foreach ($topic_users as $value) {
            $this->users[$value->user_id]['score'] = $value->topic_count * $this->topic_weight;
        }
    }

    private function calculateReplyScore()
    {
        $reply_users = Reply::query()->select(\DB::raw('user_id, count(*) as reply_count'))
                                    ->where('created_at', '>=', Carbon::now()->subDays($this->pass_day))
                                    ->groupBy('user_id')
                                    ->get();

        foreach ($reply_users as $value) {
            $reply_score = $value->reply_count * $this->reply_weight;
            if (isset($this->users[$value->user_id])) {
                $this->users[$value->user_id]['score'] += $reply_score;
            } else {
                $this->users[$value->user_id]['score'] = $reply_score;
            }
        }
    }

    private function cacheActiveUsers($active_users)
    {
        \Cache::put($this->cache_key, $active_users, $this->cache_expire_in_minites);
    }
}