<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Select;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Models\User;

class ImportUsersAction extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        // Verificar si el archivo ha sido cargado
        if ($fields->file) {
            $filePath = $fields->file->getRealPath();

            // Definir el mapeo de campos
            $mapping = [
                'name' => $fields->name_column,
                'email' => $fields->email_column,
                'password' => $fields->password_column
            ];

            // Importar los usuarios usando Fast Excel
            try {
                (new FastExcel)->import($filePath, function ($line) use ($mapping) {
                    // Verificar que las columnas existen en la línea
                    foreach ($mapping as $key => $column) {
                        if (!isset($line[$column])) {
                            throw new \Exception("La columna '{$column}' no existe en el archivo Excel.");
                        }
                    }

                    return User::create([
                        'name' => $line[$mapping['name']],
                        'email' => $line[$mapping['email']],
                        'password' => bcrypt($line[$mapping['password']]),
                    ]);
                });

                return Action::message('Usuarios importados exitosamente!');
            } catch (\Exception $e) {
                return Action::danger($e->getMessage());
            }
        } else {
            return Action::danger('No se ha cargado ningún archivo.');
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            File::make('Archivo de usuarios', 'file')->rules('required'),

            Select::make('Nombre', 'name_column')
                ->options([
                    'name' => 'name',
                    'email' => 'email',
                    'password' => 'password',
                ])
                ->rules('required'),

            Select::make('Email', 'email_column')
                ->options([
                    'name' => 'name',
                    'email' => 'email',
                    'password' => 'password',
                ])
                ->rules('required'),

            Select::make('Contraseña', 'password_column')
                ->options([
                    'name' => 'name',
                    'email' => 'email',
                    'password' => 'password',
                ])
                ->rules('required'),
        ];
    }
}
