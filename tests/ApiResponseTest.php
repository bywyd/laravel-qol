<?php

namespace Bywyd\LaravelQol\Tests;

use Bywyd\LaravelQol\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ApiResponseTest extends TestCase
{
    use ApiResponse;

    /** @test */
    public function it_returns_success_response()
    {
        $response = $this->success(['key' => 'value'], 'Operation successful');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertEquals('Operation successful', $content['message']);
        $this->assertEquals(['key' => 'value'], $content['data']);
    }

    /** @test */
    public function it_returns_error_response()
    {
        $response = $this->error('Something went wrong', 400);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertEquals('Something went wrong', $content['message']);
    }

    /** @test */
    public function it_returns_created_response()
    {
        $response = $this->created(['id' => 1], 'Resource created');

        $this->assertEquals(201, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertEquals('Resource created', $content['message']);
    }

    /** @test */
    public function it_returns_updated_response()
    {
        $response = $this->updated(['id' => 1], 'Resource updated');

        $this->assertEquals(200, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertEquals('Resource updated', $content['message']);
    }

    /** @test */
    public function it_returns_deleted_response()
    {
        $response = $this->deleted('Resource deleted');

        $this->assertEquals(200, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['success']);
        $this->assertEquals('Resource deleted', $content['message']);
        $this->assertNull($content['data']);
    }

    /** @test */
    public function it_returns_not_found_response()
    {
        $response = $this->notFound('Resource not found');

        $this->assertEquals(404, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
    }

    /** @test */
    public function it_returns_unauthorized_response()
    {
        $response = $this->unauthorized('Unauthorized access');

        $this->assertEquals(401, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertEquals('Unauthorized access', $content['message']);
    }

    /** @test */
    public function it_returns_forbidden_response()
    {
        $response = $this->forbidden('Access forbidden');

        $this->assertEquals(403, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
    }

    /** @test */
    public function it_returns_validation_error_response()
    {
        $errors = ['email' => ['Email is required']];
        $response = $this->validationError($errors, 'Validation failed');

        $this->assertEquals(422, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
        $this->assertEquals($errors, $content['errors']);
    }

    /** @test */
    public function it_returns_server_error_response()
    {
        $response = $this->serverError('Internal server error');

        $this->assertEquals(500, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['success']);
    }

    /** @test */
    public function it_returns_no_content_response()
    {
        $response = $this->noContent();

        $this->assertEquals(204, $response->getStatusCode());
    }
}
