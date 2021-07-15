<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use phpseclib3\Math\BigInteger;

/**
 * Class SocialAccount
 * @package App\Models
 *
 * @property BigInteger $id
 * @property string $provider
 * @property string $provider_user_id
 * @property BigInteger $user_id
 *
 * @method Builder whereId($value)
 * @method Builder whereProviderUserId($value)
 * @method Builder whereProvider($value)
 * @method Builder whereUserId($value)
 * @property-read  User $user
 *
 */
class SocialAccount extends Model
{
    use HasFactory;

    protected $fillable = ['provider', 'provider_user_id', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function setProviderAttribute($value): void
    {
        $this->attributes['provider'] = Str::lower($value);
    }
}
