<?php

namespace App\Observers;

use App\Models\Reply;
use App\Notifications\TopicReplied;

// creating, created, updating, updated, saving,
// saved,  deleting, deleted, restoring, restored

class ReplyObserver
{
    public function saving(Reply $reply)
    {
        $reply->content = clean($reply->content, 'user_topic_body');
    }

    public function created(Reply $reply)
    {
        $topic = $reply->topic;

        $topic->increment('reply_count');

        // 通知作者有新回复
        $topic->user->notify(new TopicReplied($reply));
    }

    public function deleted(Reply $reply)
    {
        if ($reply->topic->reply_count > 0) {
            $reply->topic->decrement('reply_count');
        }
    }
}