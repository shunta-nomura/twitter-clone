<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

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
    
     public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }
    
    public function followings()
   {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }
    
    public function unfollow($userId)
    {
        //既にフォローしているかの確認
        $exist = $this->is_following($userId);
        //相手が自分自身では無いかの確認
        $its_me = $this->id == $userId;
        
        if ($exist && !$its_me) {
            //既にフォローしていればフォローを外す
            $this->followings()->detach($userId);
            return true;
       }else {
           //未フォローであれば何もしない
           return false;
       }
    }
    public function is_following($userId)
    {
        return $this->followings()->where('follow_id', $userId)->exists();
    }
    
    public function feed_microposts()
    {
        $follow_user_ids = $this->followings()->pluck('users.id')->toArray();
        $follow_user_ids[] = $this->id;
        return Micropost::whereIn('user_id', $follow_user_ids);
    }
    
    public function is_favorites($microposts_Id)
    {
        return $this->favorites()->where('microposts_id', $microposts_Id)->exists();
    }
    public function favorites()
    {
        return $this->belongsToMany(Micropost::class,'favorites', 'user_id','microposts_id')->withTimestamps();
    }
    
    public function follow($userId)
    {
        //既にフォローしているかの確認
        $exist = $this->is_following($user_Id);
        //相手が自分自身では無いかの確認
        $its_me = $this->id == $userId;
        
        if ($exist || $its_me) {
            //既にフォローしていれば何もしない
            return false;
        }else {
            //未フォローであればフォローする
            $this->followings()->attach($userId);
            return true;
        }
    }
    
    public function favorite($microposts_Id)
    {
        //既にお気に入りしているかの確認
        $exist = $this->is_favorites($microposts_Id);
        
        if($exist) {
            //既にお気に入りならば何もしない
            return false;
        }else{
            //お気に入りでなければお気に入りにする
            $this->favorites()->attach($microposts_Id);
            return true;
        }
    }
        
        public function unfavorite($microposts_Id)
        {
            //既にお気に入りしているかの確認
        $exist = $this->is_favorites($microposts_Id);
        
        if($exist){
            $this->favorites()->detach($microposts_Id);
            return true;
        }else{
            //お気に入り出なければ何もしない
            return false;
        }
      }
    
}
