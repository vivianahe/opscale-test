<?php
namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Multiselect;
use Laravel\Nova\Http\Requests\NovaRequest;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Models\User;

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
        $selectedFields = $fields->field_mapping;

        $fieldMapping = [
            'name' => 'Nombre',
            'email' => 'Correo Electrónico',
            'created_at' => 'Fecha de Creación',
        ];

        $usersMapped = $users->map(function ($user) use ($selectedFields, $fieldMapping) {
            $mappedUser = [];
            foreach ($selectedFields as $field) {
                if ($field === 'name') {
                    $mappedUser[$fieldMapping[$field]] = $user->name;
                } elseif ($field === 'email') {
                    $mappedUser[$fieldMapping[$field]] = $user->email;
                } elseif ($field === 'created_at') {
                    $mappedUser[$fieldMapping[$field]] = $user->created_at->format('Y-m-d');
                }
            }
            return $mappedUser;
        });

        $filePath = 'support/users.xlsx';
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
        return [
            Multiselect::make('Mapeo de campos', 'field_mapping')
                ->options([
                    'name' => 'Nombre',
                    'email' => 'Correo electrónico',
                    'created_at' => 'Fecha de Creación',
                ])
                ->rules('required')
        ];
    }
}

