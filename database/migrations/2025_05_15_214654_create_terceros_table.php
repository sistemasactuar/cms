<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('terceros', function (Blueprint $table) {
            $table->id();

            // Identificación
            $table->string('nombre_tercero')->nullable();
            $table->string('tipo_id')->nullable();
            $table->string('digito_verificacion')->nullable();
            $table->string('naturaleza')->nullable();

            // Datos personales
            $table->string('sexo')->nullable();
            $table->string('estado_civil')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('nivel_educativo')->nullable();
            $table->integer('numero_hijos')->nullable();
            $table->integer('numero_dependientes')->nullable();

            // Contacto
            $table->string('direccion')->nullable();
            $table->string('barrio')->nullable();
            $table->string('telefono_fijo')->nullable();
            $table->string('celular')->nullable();
            $table->string('correo')->nullable();

            // Laborales
            $table->string('tipo_asociado')->nullable();
            $table->string('profesion')->nullable();
            $table->date('fecha_ingreso_empresa')->nullable();
            $table->decimal('sueldo_basico', 15, 2)->nullable();
            $table->decimal('otros_ingresos_mes', 15, 2)->nullable();
            $table->string('tipo_contrato')->nullable();

            // Ubicación
            $table->string('pais_nacimiento')->nullable();
            $table->string('departamento_nacimiento')->nullable();
            $table->string('ciudad_nacimiento')->nullable();

            // Estado general
            $table->string('estado_asociado')->nullable();
            $table->date('fecha_ultima_actualizacion')->nullable();

            // Autorizaciones
            $table->boolean('aut_notifi')->nullable();
            $table->boolean('aut_cons_cen_ries')->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terceros');
    }
};
