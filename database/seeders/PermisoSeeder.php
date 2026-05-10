<?php

namespace Database\Seeders;

use App\Models\Permiso;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* USUARIOS */
        $indexUser = new Permiso();
        $indexUser->permiso = 'Listar usuario';
        $indexUser->slug = 'index-user';
        $indexUser->save();

        $storeUser = new Permiso();
        $storeUser->permiso = 'Crear usuario';
        $storeUser->slug = 'store-user';
        $storeUser->save();

        $showUser = new Permiso();
        $showUser->permiso = 'Ver usuario';
        $showUser->slug = 'show-user';
        $showUser->save();

        $updateUser = new Permiso();
        $updateUser->permiso = 'Editar usuario';
        $updateUser->slug = 'update-user';
        $updateUser->save();

        $deleteUser = new Permiso();
        $deleteUser->permiso = 'Eliminar usuario';
        $deleteUser->slug = 'destroy-user';
        $deleteUser->save();

        /* ROLES */
        $indexRole = new Permiso();
        $indexRole->permiso = 'Listar Roles';
        $indexRole->slug = 'index-role';
        $indexRole->save();

        $storeRole = new Permiso();
        $storeRole->permiso = 'Crear Rol';
        $storeRole->slug = 'store-role';
        $storeRole->save();

        $updateRole = new Permiso();
        $updateRole->permiso = 'Actualizar Rol';
        $updateRole->slug = 'update-role';
        $updateRole->save();

        $deleteRole = new Permiso();
        $deleteRole->permiso = 'Eliminar Rol';
        $deleteRole->slug = 'destroy-role';
        $deleteRole->save();

        /* PERMISOS */
        $indexPermission = new Permiso();
        $indexPermission->permiso = 'Listar permisos';
        $indexPermission->slug = 'index-permission';
        $indexPermission->save();

        $storePermission = new Permiso();
        $storePermission->permiso = 'Crear permiso';
        $storePermission->slug = 'store-permission';
        $storePermission->save();

        $updatePermission = new Permiso();
        $updatePermission->permiso = 'Editar permiso';
        $updatePermission->slug = 'update-permission';
        $updatePermission->save();

        $deletePermission = new Permiso();
        $deletePermission->permiso = 'Eliminar permiso';
        $deletePermission->slug = 'destroy-permission';
        $deletePermission->save();

        /* ROL PERMISO */
        $indexAssing = new Permiso();
        $indexAssing->permiso = 'Listar rol permiso';
        $indexAssing->slug = 'index-assign';
        $indexAssing->save();

        $createAssing = new Permiso();
        $createAssing->permiso = 'Asignar permisos';
        $createAssing->slug = 'store-assign';
        $createAssing->save();

        $dataPermisos = [];
        $permisoVista = [
            ['permiso' => 'Vista dashboard'],
            ['permiso' => 'Vista contactos'],
            ['permiso' => 'Vista gestión siat'],
            ['permiso' => 'Vista configuraciones'],
        ];
        $permisosClientes=  [
            ['permiso' => 'Listar cliente'],
            ['permiso' => 'Crear cliente'],
            ['permiso' => 'Editar cliente'],
            ['permiso' => 'Eliminar cliente'],
        ];
        $permisosProducto =  [
            ['permiso' => 'Vista gestión productos'],
            ['permiso' => 'Listar producto'],
            ['permiso' => 'Crear producto'],
            ['permiso' => 'Editar producto'],
            ['permiso' => 'Anular producto'],
            ['permiso' => 'Ver producto'],
            ['permiso' => 'Importar producto'],
            ['permiso' => 'Exportar producto'],
        ];

        $permisosSucursales=  [
            ['permiso' => 'Vista gestión sucursal'],
            ['permiso' => 'Listar sucursal'],
            ['permiso' => 'Crear sucursal'],
            ['permiso' => 'Editar sucursal'],
            ['permiso' => 'Eliminar sucursal'],
        ];
        $permisosPuntoVenta=  [
            ['permiso' => 'Vista punto venta'],
            ['permiso' => 'Listar punto venta'],
            ['permiso' => 'Crear punto venta'],
            ['permiso' => 'Editar punto venta'],
            ['permiso' => 'Ver punto venta'],
            ['permiso' => 'Eliminar punto venta'],
        ];
        $permisosProforma =  [
            ['permiso' => 'Vista proformas'],
            ['permiso' => 'Listar proforma'],
            ['permiso' => 'Crear proforma'],
            ['permiso' => 'Editar proforma'],
            ['permiso' => 'Ver proforma'],
            ['permiso' => 'Eliminar proforma'],
        ];
        $permisosVenta =  [
            ['permiso' => 'Listar venta'],
            ['permiso' => 'Crear venta'],
            ['permiso' => 'Eliminar venta'],
        ];
        $permisosEventoSignificativo =  [
            ['permiso' => 'Evento significativo'],
        ];
        $permisosVarios = [
            ['permiso' => 'Descuento total'],
            ['permiso' => 'Descuento parcial'],
        ];
        $dataPermisos = array_merge($permisoVista,$permisosProducto,$permisosClientes,$permisosSucursales,$permisosPuntoVenta,$permisosProforma,$permisosVenta,$permisosEventoSignificativo,$permisosVarios);

//        dd($dataPermisos);
        foreach($dataPermisos as $data){
            Permiso::create($data);
        }

    }
}
