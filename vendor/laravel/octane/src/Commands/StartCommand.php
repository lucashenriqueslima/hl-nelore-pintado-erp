<?php

namespace Laravel\Octane\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\SignalableCommandInterface;

#[AsCommand(name: 'octane:start')]
class StartCommand extends Command implements SignalableCommandInterface
{
    use Concerns\InteractsWithServers;

    /**
     * The command's signature.
     *
     * @var string
     */
    public $signature = 'octane:start
                    {--server= : The server that should be used to serve the application}
                    {--host= : The IP address the server should bind to}
                    {--port= : The port the server should be available on [default: "8000"]}
                    {--admin-port= : The port the admin server should be available on [FrankenPHP only]}
                    {--rpc-host= : The RPC IP address the server should bind to}
                    {--rpc-port= : The RPC port the server should be available on}
                    {--workers=auto : The number of workers that should be available to handle requests}
                    {--task-workers=auto : The number of task workers that should be available to handle tasks}
                    {--max-requests=500 : The number of requests to process before reloading the server}
                    {--rr-config= : The path to the RoadRunner .rr.yaml file}
                    {--caddyfile= : The path to the FrankenPHP Caddyfile file}
                    {--https : Enable HTTPS, HTTP/2, and HTTP/3, and automatically generate and renew certificates [FrankenPHP only]}
                    {--http-redirect : Enable HTTP to HTTPS redirection (only enabled if --https is passed) [FrankenPHP only]}
                    {--watch : Automatically reload the server when the application is modified}
                    {--poll : Use file system polling while watching in order to watch files over a network}
                    {--log-level= : Log messages at or above the specified log level}';

    /**
     * The command's description.
     *
     * @var string
     */
    public $description = 'Start the Octane server';

    /**
     * Handle the command.
     *
     * @return int
     */
    public function handle()
    {
        $server = $this->option('server') ?: config('octane.server');

        return match ($server) {
            'swoole' => $this->startSwooleServer(),
            'roadrunner' => $this->startRoadRunnerServer(),
            'frankenphp' => $this->startFrankenPhpServer(),
            default => $this->invalidServer($server),
        };
    }

    /**
     * Start the Swoole server for Octane.
     *
     * @return int
     */
    protected function startSwooleServer()
    {
        return $this->call('octane:swoole', [
            '--host' => $this->getHost(),
            '--port' => $this->getPort(),
            '--workers' => $this->option('workers') ?: config('octane.workers', 'auto'),
            '--task-workers' => $this->option('task-workers') ?: config('octane.task_workers', 'auto'),
            '--max-requests' => $this->option('max-requests') ?: config('octane.max_requests', 500),
            '--watch' => $this->option('watch'),
            '--poll' => $this->option('poll'),
        ]);
    }

    /**
     * Start the RoadRunner server for Octane.
     *
     * @return int
     */
    protected function startRoadRunnerServer()
    {
        return $this->call('octane:roadrunner', [
            '--host' => $this->getHost(),
            '--port' => $this->getPort(),
            '--rpc-host' => $this->option('rpc-host'),
            '--rpc-port' => $this->option('rpc-port'),
            '--workers' => $this->option('workers') ?: config('octane.workers', 'auto'),
            '--max-requests' => $this->option('max-requests') ?: config('octane.max_requests', 500),
            '--rr-config' => $this->option('rr-config'),
            '--watch' => $this->option('watch'),
            '--poll' => $this->option('poll'),
            '--log-level' => $this->option('log-level'),
        ]);
    }

    /**
     * Start the FrankenPHP server for Octane.
     *
     * @return int
     */
    protected function startFrankenPhpServer()
    {
        return $this->call('octane:frankenphp', [
            '--host' => $this->getHost(),
            '--port' => $this->getPort(),
            '--admin-port' => $this->option('admin-port'),
            '--workers' => $this->option('workers') ?: config('octane.workers', 'auto'),
            '--max-requests' => $this->option('max-requests') ?: config('octane.max_requests', 500),
            '--caddyfile' => $this->option('caddyfile'),
            '--https' => $this->option('https'),
            '--http-redirect' => $this->option('http-redirect'),
            '--watch' => $this->option('watch'),
            '--poll' => $this->option('poll'),
            '--log-level' => $this->option('log-level'),
        ]);
    }

    /**
     * Inform the user that the server type is invalid.
     *
     * @return int
     */
    protected function invalidServer(string $server)
    {
        $this->components->error("Invalid server: {$server}.");

        return 1;
    }

    /**
     * Stop the server.
     *
     * @return void
     */
    protected function stopServer()
    {
        $server = $this->option('server') ?: config('octane.server');

        $this->callSilent('octane:stop', [
            '--server' => $server,
        ]);
    }
}
