<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Vite no compila assets en CI. withoutVite() reemplaza @vite() con
        // un stub para que los Feature tests que renderizan vistas no fallen.
        $this->withoutVite();
        // CSRF está cubierto por los tests de Dusk. Los feature tests verifican
        // lógica de negocio, no tokens de formulario.
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }
}
