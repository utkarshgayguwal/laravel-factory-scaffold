<?php

namespace utkarshgayguwal\FactoryScaffold\Commands;

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
        $factoryStub = "<?php\n\nnamespace Database\Factories;\n\n";
        $factoryStub .= "use {$model};\n";
        $factoryStub .= "use Illuminate\Database\Eloquent\Factories\Factory;\n\n";
        $factoryStub .= "class ".class_basename($model)."Factory extends Factory\n{\n";
        $factoryStub .= "    protected \$model = {$model}::class;\n\n";
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
        $type = $column['type'];
        
        // Handle foreign keys (assuming naming convention like 'user_id')
        if (Str::endsWith($name, '_id')) {
            return 'rand(1, 5)';
        }
        
        // Map column types to faker methods
        switch ($type) {
            case 'string':
                if (Str::contains($name, 'email')) {
                    return '$this->faker->unique()->safeEmail()';
                } elseif (Str::contains($name, 'name')) {
                    return '$this->faker->name()';
                }
                return 'Str::random(10)';
            case 'text':
                return '$this->faker->paragraph()';
            case 'integer':
            case 'bigint':
                return '$this->faker->randomNumber()';
            case 'boolean':
                return '$this->faker->boolean()';
            case 'date':
            case 'datetime':
                return '$this->faker->dateTime()';
            case 'float':
            case 'decimal':
                return '$this->faker->randomFloat(2)';
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
        $stub .= "use {$model};\n\n";
        $stub .= "class {$seederName} extends Seeder\n{\n";
        $stub .= "    public function run()\n    {\n";
        $stub .= "        {$model}::factory()->count({$count})->create();\n";
        $stub .= "    }\n}\n";
        
        return $stub;
    }
    
    protected function writeFactoryFile($model, $content)
    {
        $modelBaseName = class_basename($model);
        $path = database_path("factories/{$modelBaseName}Factory.php");
        
        file_put_contents($path, $content);
    }
    
    protected function writeSeederFile($model, $content)
    {
        $modelBaseName = class_basename($model);
        $path = database_path("seeders/{$modelBaseName}Seeder.php");
        
        file_put_contents($path, $content);
    }
}