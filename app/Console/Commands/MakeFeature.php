<?php

namespace App\Console\Commands;

use App\Traits\CommandHandler;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MakeFeature extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:feature {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new feature with controller,service,repository, and model';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targets = [
            'controller' => 'Controller',
            'service' => 'Service',
            'repository' => 'Repository',
            'model' => ''
        ];

        $defaultName = $this->argument('name');

        foreach ($targets as $k => $v) {
            $stub = File::get(__DIR__ . "/Stubs/$k.stub");
            $data = $this->prepareData("Features/$defaultName/", $k, $defaultName, $v);

            if (!$data) {
                break;
            }

            array_unshift($data['additionalDir'], str_replace($v, '', $data['name']));
            $this->storeData($data, $stub);

            $this->info("$k [" . str_replace(base_path() . '/', '', $data['path']) . "] created successfully.");
        }
    }

    public function prepareData(string $appFolder, string $type, string $defaultName, string $suffix = ''): ?array
    {
        $explodedName = array_filter(explode('/', $defaultName), fn($item) => $item != '');
        $name = end($explodedName);
        $additionalDir = array_slice($explodedName, 0, -1);
        $path = $appFolder . implode('/', $additionalDir) . (count($additionalDir) > 0 ? '/' : '')  . end($explodedName) . "$suffix.php";

        if (Storage::disk('local_app')->exists($path)) {
            $this->error("$type already exists!");
            return null;
        }

        $result = [
            'path' => $path,
            'name' => $name,
            'arguments' => $defaultName,
            'additionalDir' => $additionalDir,
        ];

        return $result;
    }

    public function storeData(array $data, string $stub, array $addKeySearch = [], array $addReplace = [])
    {
        $content = str_replace(\array_merge(['{{name}}', '{{additionalDir}}'], $addKeySearch), \array_merge([$data['name'], count($data['additionalDir']) > 0 ? '\\' . implode('\\', $data['additionalDir']) : ''], $addReplace), $stub);
        Storage::disk('local_app')->put($data['path'], $content);
    }
}
