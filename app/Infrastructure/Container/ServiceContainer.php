<?php

namespace App\Infrastructure\Container;

use App\Domain\Repository\BookRepositoryInterface;
use App\Domain\Repository\BorrowerRepositoryInterface;
use App\Domain\Repository\LoanRepositoryInterface;
use App\Infrastructure\Repository\PdoBookRepository;
use App\Infrastructure\Repository\PdoBorrowerRepository;
use App\Infrastructure\Repository\PdoLoanRepository;
use PDO;

/**
 * Simple Service Container
 * 
 * Manages dependency injection for the application.
 * This is a minimal implementation - for larger apps consider Symfony DI or PHP-DI.
 */
class ServiceContainer
{
    private array $services = [];
    private array $factories = [];
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
    }

    public function get(string $id): mixed
    {
        if (!isset($this->services[$id])) {
            if (!isset($this->factories[$id])) {
                throw new \RuntimeException("Service not found: {$id}");
            }
            $this->services[$id] = ($this->factories[$id])($this);
        }
        return $this->services[$id];
    }

    /**
     * Initialize default services
     */
    public function initialize(): void
    {
        // PDO Database Connection
        $this->register(PDO::class, function () {
            $config = require __DIR__ . '/../../../config/database.php';
            $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            return new PDO($dsn, $config['username'], $config['password'], $options);
        });

        // Repositories
        $this->register(BookRepositoryInterface::class, function (self $c) {
            return new PdoBookRepository($c->get(PDO::class));
        });

        $this->register(BorrowerRepositoryInterface::class, function (self $c) {
            return new PdoBorrowerRepository($c->get(PDO::class));
        });

        $this->register(LoanRepositoryInterface::class, function (self $c) {
            return new PdoLoanRepository($c->get(PDO::class));
        });
    }
}
