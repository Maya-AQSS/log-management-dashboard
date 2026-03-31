<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Vite no compila assets en CI. withoutVite() reemplaza @vite() con
        // un stub para que los Feature tests que renderizan vistas no fallen.
        $this->withoutVite();
    }
}
