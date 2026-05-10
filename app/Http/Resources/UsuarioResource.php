<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'idRol' => $this->roles[0]->id,
            'roles' => $this->roles[0]->rol,
            /* 'roles' => array_map(
                function ($rol) {
                    return $rol['rol'];
                },
                $this->roles->toArray()
            ), */
            'email' => $this->email,
            'nombres' => $this->perfil->nombres,
            'apellidos' => $this->perfil->apellidos,
            'telefono' => $this->perfil->telefono,
            'celular' => $this->perfil->celular,
            'foto' => $this->perfil->foto,
            'estado' => $this->estado,
            'tipo_impresion' => $this->tipoImpresion[0]->tipo,
        ];
    }
}
