<?php

namespace App\Models;
use App\Models\User;
use App\Models\Like;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','body'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function scopeWithLikes(Builder $query)
    {
        $query->leftJoinSub(
            'select tweet_id, sum(liked) likes, sum(!liked) dislikes from likes group by tweet_id',
            'likes',
            'likes.tweet_id',
            'tweets.id'
        );
    }
    public function like($user = null,$liked = true){
        $this->likes()->updateOrCreate([
            'user_id' => $user ? $user->id : auth()->id()
        ],[
            'liked' => $liked
        ]);
    }
    public function dislike($user = null)
    {
        return $this->like($user, false);
    }
    public function isLikedBy(){
        return (bool) $this->likes()->where('tweet_id', $this->id)->where('liked', true)->count();
    }
    public function isDislikedBy(){
        return (bool) $this->likes()->where('tweet_id', $this->id)->where('liked', false)->count();
    }
    public function likes(){
        return $this->hasMany(Like::class);
    }
}