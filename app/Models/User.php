<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fcm_token',
        'email',
        'password',
        'role',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getRoleAttribute()
    {
        // Return the role of the user
        return $this->attributes['role'];
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'user_id', 'id');
    }
    
    public function vet()
    {
        return $this->hasOne(Vet::class, 'user_id', 'id');
    }
    
    public function petShop()
    {
        return $this->hasOne(PetShop::class, 'user_id', 'id');
    }

    public function messages(){
        return $this->hasMany(ChatMessages::class, 'user_id');
    }

    public function sender(){
        return $this->hasOne(Chat::class, 'sender_id');
    }

    public function receiver(){
        return $this->hasOne(Chat::class, 'receiver_id');
    }
    
    public function ratingSender(){
        return $this->hasMany(Rating::class, 'sender_id');
    }

    public function ratingReceiver(){
        return $this->hasMany(Rating::class, 'receiver_id');
    }
}