<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Artisan;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class GeneratorLayerCommand extends Command
{
    protected $signature = 'crud:generator
    {name : Class (singular) for example User} {--fields=*}';

    protected $description = 'Create crud operations';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $name = $this->argument('name');
        $fields = $this->option('fields');
        $this->controller($name);
        $this->service($name);
        $this->model($name, $fields);
        $this->request($name);
        $nameController = $name . "Controller";

        $this->routes($name, $nameController);
        $this->abilities($name);
        Artisan::call(command: 'make:migration create_' . strtolower($name) . '_table --create=' . strtolower($name));
    }

    protected function controller($name)
    {
        $controllerTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}'
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower(Str::snake($name))
            ],
            $this->getStub('Controller')
        );

        file_put_contents(app_path("/Http/Controllers/{$name}Controller.php"), $controllerTemplate);
    }
    protected function service($name)
    {
        $serviceTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}'
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower($name)
            ],
            $this->getStub('Service')
        );

        file_put_contents(app_path("/Services/{$name}Service.php"), $serviceTemplate);
    }
    protected function model($name, $fields)
    {
        $modelTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('Model')
        );
        $fields = '"' . implode('","', $fields) . '"';
        $modelTemplate = str_replace(
            ['{{fillable}}'],
            [$fields],
            $modelTemplate
        );

        file_put_contents(app_path("/Models/{$name}.php"), $modelTemplate);
    }

    protected function request($name)
    {
        $requestTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('Request')
        );

        if (!file_exists($path = app_path('/Http/Requests')))
            mkdir($path, 0777, true);

        file_put_contents(app_path("/Http/Requests/{$name}Request.php"), $requestTemplate);
    }


    protected function getStub($type)
    {
        return file_get_contents(resource_path("stubs/$type.stub"));
    }

    protected function routes($name, $nameController)
    {
        // File::append(base_path('routes/api.php'), "\n \n Route::apiResource('" . Str::plural(strtolower($name)) . "'" . str_replace(".", "", ",App\Http\Controllers\.$nameController.::class)") . "->middleware(['check.auth']);");

        File::append(base_path('routes/api.php'), "\n \n Route::prefix('" . Str::plural(strtolower($name)) . "')->group(function () {");
        File::append(base_path('routes/api.php'), "\n \n Route::get('/'"  .  str_replace(".", "", ",[App\Http\Controllers\.$nameController.::class, 'index'])") . "->middleware('abilities:" . strtolower($name) . "_list');");
        File::append(base_path('routes/api.php'), "\n \n Route::post('/'" .  str_replace(".", "", ",[App\Http\Controllers\.$nameController.::class, 'store'])") . "->middleware('abilities:" . strtolower($name) . "_insert');");
        File::append(base_path('routes/api.php'), "\n \n Route::get('/{id}'"  . str_replace(".", "", ",[App\Http\Controllers\.$nameController.::class, 'show'])") . "->middleware('abilities:" . strtolower($name) . "_by_id');");
        File::append(base_path('routes/api.php'), "\n \n Route::put('/{id}'" .  str_replace(".", "", ",[App\Http\Controllers\.$nameController.::class, 'update'])") . "->middleware('abilities:" . strtolower($name) . "_edit');");
        File::append(base_path('routes/api.php'), "\n \n Route::delete('/{id}'"  . str_replace(".", "", ",[App\Http\Controllers\.$nameController.::class, 'destroy'])") . "->middleware('abilities:" . strtolower($name) . "_delete');");
        File::append(base_path('routes/api.php'), "\n \n });");

    }

    protected function abilities($name)
    {
        File::append(base_path('database/seeders/AbilitySeeder.php'), "\n \n
        [
            'name' => 'Listar " . strtolower($name) . "',
            'slug' => '" . strtolower($name) . "_list',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ],");
        File::append(base_path('database/seeders/AbilitySeeder.php'), "\n \n
        [
            'name' => 'Cadastrar " . strtolower($name) . "',
            'slug' => '" . strtolower($name) . "_insert',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ],");
        File::append(base_path('database/seeders/AbilitySeeder.php'), "\n \n
        [
            'name' => 'Editar " . strtolower($name) . "',
            'slug' => '" . strtolower($name) . "_by_id',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ],");
        File::append(base_path('database/seeders/AbilitySeeder.php'), "\n \n
        [
            'name' => 'Seleciona " . strtolower($name) . "',
            'slug' => '" . strtolower($name) . "_edit',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ],");
        File::append(base_path('database/seeders/AbilitySeeder.php'), "\n \n
        [
            'name' => 'Deletar " . strtolower($name) . "',
            'slug' => '" . strtolower($name) . "_delete',
            'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
        ],");

    }
}
