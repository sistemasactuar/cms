<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class BootstrapAdminAccessCommand extends Command
{
    protected $signature = 'access:bootstrap-admin
        {email=admin@appactuar.local : Correo del usuario admin}
        {--name=Administrador : Nombre del usuario}
        {--password= : Password a establecer}
        {--force : No pedir confirmacion}';

    protected $description = 'Crea o restablece un usuario administrador de acceso para el panel.';

    public function handle(): int
    {
        $email = trim((string) $this->argument('email'));
        $name = trim((string) $this->option('name'));
        $password = (string) $this->option('password');

        if ($email === '') {
            $this->error('Debes indicar un correo valido.');
            return self::INVALID;
        }

        if ($password === '') {
            $this->error('Debes indicar --password para crear o restablecer el acceso.');
            return self::INVALID;
        }

        if (!$this->option('force') && !$this->confirm("Se creara o actualizara el acceso admin para {$email}. Deseas continuar?")) {
            $this->warn('Operacion cancelada.');
            return self::INVALID;
        }

        foreach (['admin', 'Superadmin', 'superadmin'] as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        $user = User::query()->firstOrNew(['email' => $email]);
        $user->name = $name !== '' ? $name : 'Administrador';
        $user->password = Hash::make($password);
        $user->email_verified_at ??= now();
        $user->save();

        $user->syncRoles(['admin', 'Superadmin', 'superadmin']);

        $this->info("Acceso admin listo para {$email}.");
        $this->line('Roles asignados: admin, Superadmin, superadmin.');

        return self::SUCCESS;
    }
}
