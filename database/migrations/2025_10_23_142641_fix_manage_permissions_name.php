<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixManagePermissionsName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Actualizar el nombre del permiso de 'manage_permissions' a 'manage-permissions'
        \DB::table('permissions')
            ->where('name', 'manage_permissions')
            ->update(['name' => 'manage-permissions']);

        // Limpiar cache de permisos de Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir el cambio
        \DB::table('permissions')
            ->where('name', 'manage-permissions')
            ->update(['name' => 'manage_permissions']);

        // Limpiar cache de permisos de Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
