<?php

namespace App\Models;

use App\Http\Traits\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Usuario extends Authenticatable implements JWTSubject
{
    use HasFactory, SoftDeletes, Notifiable, QueryFilter;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $table = 'usuarios'; // nombre de la tabla
    public $primaryKey = 'id'; // campo de llave primaria
    protected $guarded = ['id'];
    protected $fillable = [
        'username',
        'password',
        'email',
        'resent',
        'estado',
    ]; // atributos de la tabla

    //protected $remember_token = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'superAdmin', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /* Relación de tablas 1:1 usuario con persona */
    public function perfil()
    {
        /* 1 usuario pertenece a una persona */
        return $this->hasOne(Perfil::class, 'usuario_id', 'id'); //->select('id','nombres', 'apellidos', 'telefono', 'celular', 'foto');
    }

    /* Relación de la tabla usuario con roles */
    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'usuario_id', 'rol_id');
    }
    //relacion a impresion
    public function tipoImpresion()
    {
        return $this->belongsToMany(TipoImpresion::class,'usuario_tipo_impresion')->withTimestamps();
    }
    /**
     * Aquí, estamos iterando a través de los roles y
     * verificando por el campo slug, si ese rol específico existe.
     */
    public function hasRole(...$roles): bool
    {
        foreach ($roles as $rol) {
            if ($this->roles->contains('slug', $rol)) {
                return true;
            }
        }
        return false;
    }

    //verifica que tenga el permiso asignado segun el rol
    public function hasPermission($slug): bool
    {
        $permissions = [];

        $cacheKey = "role_permissions_{$this->id}";

        if (Cache::has($cacheKey)) {
            $permissions = Cache::get($cacheKey);
        }

        if (empty($permissions)) {
            foreach ($this->roles as $role) {
                foreach ($role->permisos as $permission) {
                    $permissions[$permission->slug] = true;
//                    $permissions[] = $permission->slug;
                }
            }
            Cache::put($cacheKey, $permissions, now()->addMinutes(10));
        }
//        dd($permissions);
//        foreach ($permissions as $permission) {
//            if ($permission->slug === $slug) {
//                return true;
//            }
//        }
//        return in_array($slug, $permissions);
        return isset($permissions[$slug]);
    }

    /*verifica que tenga el permisos asignado segun el rol y si se envia requireAll en
    *  true valida que tenga todo los permisos enviados
    */
    public function hasPermissionTo(array $slugs, bool $requireAll = false): bool
    {
        $permissionsFound = [];
        foreach ($slugs as $slug) {
            if ($this->hasPermission($slug))
                $permissionsFound[] = $slug;
        }
        if ((!$requireAll && count($permissionsFound) > 0) || ($requireAll && count($slugs) === count($permissionsFound)))
            return true;
        return false;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
