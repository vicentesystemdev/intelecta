<?php

namespace App\Console\Commands;

use App\Domains\Postulantes\Actions\VincularUsuarioPostulanteAction;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class VincularPostulante extends Command
{
    protected $signature = 'intelecta:vincular-postulante
        {user_id : ID explícito de la cuenta Estudiante}
        {id_post : ID explícito del expediente confirmado}
        {--actor= : ID del Super Administrador responsable}
        {--motivo= : Referencia de la revisión de identidad, sin PII innecesaria}';

    protected $description = 'Confirma un vínculo por IDs, sin buscar ni inferir identidad por correo';

    public function handle(VincularUsuarioPostulanteAction $action): int
    {
        foreach ([$this->argument('user_id'), $this->argument('id_post'), $this->option('actor')] as $id) {
            if (filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
                $this->error('user_id, id_post y --actor deben ser IDs enteros positivos explícitos.');

                return self::FAILURE;
            }
        }
        $actor = User::find((int) $this->option('actor'));
        if (! $actor?->hasRole('Super Administrador')) {
            $this->error('El actor debe ser un Super Administrador existente.');

            return self::FAILURE;
        }

        $this->warn('Esta operación requiere revisión humana previa. Una coincidencia de correo no demuestra identidad.');
        if (! $this->confirm('¿Confirmas el vínculo User '.$this->argument('user_id').' ↔ Postulante '.$this->argument('id_post').' en la BD '.config('database.connections.'.config('database.default').'.database').'?', false)) {
            $this->info('Cancelado: no se modificaron vínculos.');

            return self::FAILURE;
        }

        try {
            $action->execute((int) $this->argument('user_id'), (int) $this->argument('id_post'), $actor, (string) $this->option('motivo'));
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $messages) {
                $this->error(implode(' ', $messages));
            }

            return self::FAILURE;
        } catch (AuthorizationException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
        $this->info('Vínculo confirmado por IDs. No se modificaron correos ni roles.');

        return self::SUCCESS;
    }
}
