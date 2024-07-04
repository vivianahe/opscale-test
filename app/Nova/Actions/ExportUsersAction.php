<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ExportUsersAction extends Action
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
        $users = User::all();

        $filePath = 'support/users.xlsx';

        $usersMapped = $users->map(function ($user) {
            return [
                'Nombre' => $user->name,
                'Correo Electrónico' => $user->email,
                'Fecha de Creación' => $user->created_at->format('Y-m-d')
            ];
        });

        (new FastExcel($usersMapped))->export(public_path($filePath));

        return Action::download(
            url($filePath),
            'users.xlsx'
        );
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}
