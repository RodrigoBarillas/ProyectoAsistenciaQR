<?php

namespace Tests\Feature;

use Tests\TestCase;

class CorsTest extends TestCase
{
    public function test_preflight_responde_con_cors_headers_para_un_origen_permitido(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:5173',
            'Access-Control-Request-Method' => 'POST',
        ])->options('/api/v1/auth/login');

        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
    }

    public function test_preflight_no_incluye_el_origen_para_un_origen_no_permitido(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'https://sitio-no-autorizado.example.com',
            'Access-Control-Request-Method' => 'POST',
        ])->options('/api/v1/auth/login');

        $this->assertNotSame(
            'https://sitio-no-autorizado.example.com',
            $response->headers->get('Access-Control-Allow-Origin'),
        );
    }

    public function test_respuesta_real_incluye_el_header_cors_para_un_origen_permitido(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
        ])->postJson('/api/v1/auth/login', []);

        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
    }
}
