<?php

namespace UtkarshGayguwal\FactoryScaffold\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ScaffoldFactoryCommand extends Command
{
    protected $signature = 'make:scaffold {model} {--count=10}';
    protected $description = 'Generate factory and seeder for a model';

    public function handle()
    {
        $model = $this->argument('model');
        $count = $this->option('count');
        $model = str_replace('/', '\\', $model);
    
        // Verify the model exists
        if (!class_exists($model)) {
            $this->error("Model {$model} does not exist!");
            return;
        }

        // Extract table name from model
        $modelInstance = app($model);
        $tableName = $modelInstance->getTable();
        
        // Get table columns information
        $columns = DB::getSchemaBuilder()->getColumns($tableName);
        
        // Generate factory content
        $factoryContent = $this->generateFactoryContent($model, $columns);

        
        // Generate seeder content
        $seederContent = $this->generateSeederContent($model, $count);
        
        // Write files
        $this->writeFactoryFile($model, $factoryContent);
        $this->writeSeederFile($model, $seederContent);
        
        $this->info("Factory and Seeder for {$model} created successfully!");
    }
    
    protected function generateFactoryContent($model, $columns)
    {
        $modelBaseName = class_basename($model);
    
        $namespaceParts = explode('\\', $model);
        $subnamespace = '';
        
        $modelsKey = array_search('Models', $namespaceParts);
        if ($modelsKey !== false && count($namespaceParts) > $modelsKey + 2) {
            $subfolders = array_slice($namespaceParts, $modelsKey + 1, -1);
            $subnamespace = '\\' . implode('\\', $subfolders);
        }

        $factoryStub = "<?php\n\nnamespace Database\Factories{$subnamespace};\n\n";
        $factoryStub .= "use {$model};\n";
        $factoryStub .= "use Illuminate\Database\Eloquent\Factories\Factory;\n";
        $factoryStub .= "use Carbon\\Carbon;\n\n";
        $factoryStub .= "class ".class_basename($model)."Factory extends Factory\n{\n";
        $factoryStub .= "    protected \$model = " . class_basename($model) . "::class;\n\n";
        $factoryStub .= "    public function definition()\n    {\n        return [\n";

        foreach ($columns as $column) {
            if ($column['name'] === 'id') continue;
            
            $factoryStub .= "            '{$column['name']}' => ";
            $factoryStub .= $this->getFakeDataForColumn($column);
            $factoryStub .= ",\n";
        }

        $factoryStub .= "        ];\n    }\n}\n";
        return $factoryStub;
    }
    
    protected function getFakeDataForColumn($column)
    {
        $name = $column['name'];
        $type = $column['type_name'];

        // Handle special column names first
        if ($name === 'password') {
            return 'bcrypt("password")';
        }

        if (Str::endsWith($name, '_id')) {
            return 'rand(1, 5)';
        }

        if (Str::contains($name, 'email') && $type === 'varchar') {
            return '$this->faker->unique()->safeEmail()';
        }

        if (Str::contains($name, 'name')) {
            return '$this->faker->name()';
        }

        if (Str::contains($name, ['phone', 'mobile'])) {
            return '$this->faker->phoneNumber()';
        }

        if (Str::contains($name, 'address')) {
            return '$this->faker->address()';
        }

        // Handle data types
        switch ($type) {
            case 'varchar':
                return '$this->faker->word()';
            case 'text':
                return '$this->faker->paragraph()';
            case 'integer':
            case 'bigint':
                return '$this->faker->randomNumber()';
            case 'boolean':
                return '$this->faker->boolean()';
            case 'date':
                return 'Carbon::now()->format("Y-m-d")';
            case 'datetime':
            case 'timestamp':
                return 'Carbon::now()';
            case 'float':
            case 'decimal':
                return '$this->faker->randomFloat(2, 0, 1000)';
            default:
                return 'null';
        }
    }
    
    protected function generateSeederContent($model, $count)
    {
        $modelBaseName = class_basename($model);
        $seederName = "{$modelBaseName}Seeder";
        
        $stub = "<?php\n\nnamespace Database\Seeders;\n\n";
        $stub .= "use Illuminate\Database\Seeder;\n";
        $stub .= "use Illuminate\Support\Facades\DB;\n";
        $stub .= "use {$model};\n\n";
        $stub .= "class {$seederName} extends Seeder\n{\n";
        $stub .= "    public function run()\n    {\n";
        $stub .= "        DB::statement('SET FOREIGN_KEY_CHECKS=0');\n";
        $stub .= "        " . class_basename($model) . "::truncate();\n";
        $stub .= "        DB::statement('SET FOREIGN_KEY_CHECKS=1');\n";
        $stub .= "        " . class_basename($model) . "::factory()->count({$count})->create();\n";
        $stub .= "    }\n}\n";
        
        return $stub;
    }
    
    protected function writeFactoryFile($model, $content)
    {
        $modelBaseName = class_basename($model);
    
        // Extract subfolder path from namespace (e.g., "Leave" from "App\Models\Leave\User")
        $namespaceParts = explode('\\', $model);
        $subfolderPath = '';
        
        // Find the part after "Models" in the namespace
        $modelsKey = array_search('Models', $namespaceParts);
        if ($modelsKey !== false && count($namespaceParts) > $modelsKey + 2) {
            $subfolders = array_slice($namespaceParts, $modelsKey + 1, -1);
            $subfolderPath = implode('/', $subfolders);
        }
        
        // Create the full directory path
        $factoryDir = database_path("factories/{$subfolderPath}");
        if (!file_exists($factoryDir)) {
            mkdir($factoryDir, 0755, true);
        }

        $path = "{$factoryDir}/{$modelBaseName}Factory.php";
        
        file_put_contents($path, $content);
    }
    
    protected function writeSeederFile($model, $content)
    {
        $modelBaseName = class_basename($model);
        $path = database_path("seeders/{$modelBaseName}Seeder.php");
        
        file_put_contents($path, $content);
    }
}