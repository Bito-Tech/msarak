<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // تعطيل Vite في بيئة الاختبارات لتجنب خطأ manifest.json
        $this->withoutVite();
    }
}