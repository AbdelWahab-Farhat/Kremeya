<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class ExportPostmanCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:postman';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export API routes to a Postman Collection JSON file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $routes     = Route::getRoutes();
        $collection = [
            'info' => [
                'name'   => config('app.name') . ' API',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => [],
        ];

        $groupedRoutes = [];

        foreach ($routes as $route) {
            $uri = $route->uri();

            // Only export API routes
            if (! Str::startsWith($uri, 'api/')) {
                continue;
            }

            // Skip ignition/debug routes if any
            if (Str::contains($uri, '_ignition')) {
                continue;
            }

            $method     = $route->methods()[0];
            $actionName = $route->getActionName();

            // Determine group name based on Controller
            $groupName = 'General';
            if ($actionName !== 'Closure') {
                $controller = Str::before(Str::afterLast($actionName, '\\'), 'Controller');
                if ($controller) {
                    $groupName = Str::plural($controller);
                }
            }

            if (! isset($groupedRoutes[$groupName])) {
                $groupedRoutes[$groupName] = [];
            }

            // Remove api/v1 prefix specifically as requested
            $cleanUri = Str::replaceFirst('api/v1/', '', $uri);

            $groupedRoutes[$groupName][] = [
                'name'    => $cleanUri,
                'request' => [
                    'method' => $method,
                    'header' => [
                        [
                            'key'   => 'Accept',
                            'value' => 'application/json',
                            'type'  => 'text',
                        ],
                        [
                            'key'   => 'Content-Type',
                            'value' => 'application/json',
                            'type'  => 'text',
                        ],
                    ],
                    'url'    => [
                        'raw'  => '{{base_url}}/' . $cleanUri,
                        'host' => ['{{base_url}}'],
                        'path' => explode('/', $cleanUri),
                    ],
                ],
            ];
        }

        foreach ($groupedRoutes as $groupName => $items) {
            $collection['item'][] = [
                'name' => $groupName,
                'item' => $items,
            ];
        }

        $json = json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $path = storage_path('app/postman_collection.json');
        file_put_contents($path, $json);

        $this->info("Postman collection exported to: $path");
        $this->info("You can import this file into Postman.");
    }
}
