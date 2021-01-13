<?php

namespace App\Interfaces;


interface Notifiable
{
    public static function notify($actor_id, $recipient_id, $post_id);
}
