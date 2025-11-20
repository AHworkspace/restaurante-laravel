<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /*public function rol()
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    public function hasRole($roleName)
    {
        return optional($this->rol)->nombre === $roleName;
    }*/

    /**
     * Verifica si el usuario es administrador
     */
    /*public function isAdmin(): bool
    {
        return $this->role === 'administrador';
    }
*/
    /**
     * Verifica si el usuario es cocinero
     */
  /*  public function isCocinero(): bool
    {
        return $this->role === 'cocinero';
    }*/

    /**
     * Verifica si el usuario es cajero
     */
 /*   public function isCajero(): bool
    {
        return $this->role === 'cajero';
    }*/
}
