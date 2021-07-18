<?php

namespace App\Models;

use App\Mail\ResetMail;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\HasApiTokens;
use phpseclib3\Math\BigInteger;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 * @package App\Models
 *
 * @property BigInteger $id
 * @property string $name
 * @property string $email
 * @property string $avatar
 * @property string $phone_number
 * @property string $password
 * @property string $code
 * @property Carbon $email_verified_at
 *
 * @method Builder whereId($value)
 * @method Builder whereEmail($value)
 * @property-read  SocialAccount[] $social_accounts
 * @property-read  Address[] $addresses
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    public const FACEBOOK_PROVIDER = 'facebook';
    public const GOOGLE_PROVIDER = 'google';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'avatar',
        'code'
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * @throws \Exception
     */
    public function generateActivationCode(): User
    {
        $email = $this->email;
        //send email here
        $token = random_int(100000, 999999);
        $data = ['name' => $this->name, 'verification_code' => $token];

//        // Send Email
//        Mail::to([
//            'email' => $email
//        ])->send(new VerifyMail($data));

        $this->code = $token;
        $this->email_verified_at = null;
        return $this;
    }


    /**
     * @param string $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $data = ['name' => $this->name, 'token' => $token];
        Mail::to([
            'email' => $this->email
        ])->send(new ResetMail($data));
    }

    /**
     * @return HasMany
     */
    public function social_accounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

}
