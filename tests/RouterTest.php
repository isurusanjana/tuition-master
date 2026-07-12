<?php

use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function testNamedRouteGeneratesUrl(): void
    {
        $router = new Router();
        $router->get('/users/{id}/edit', 'UserController@edit', 'users.edit');
        $url = $router->url('users.edit', ['id' => 42]);
        $this->assertStringContainsString('/users/42/edit', $url);
    }

    public function testUnknownRouteFallsBackToRoot(): void
    {
        $router = new Router();
        $this->assertSame(APP_URL . '/', $router->url('does.not.exist'));
    }

    public function testUrlWithMultipleParams(): void
    {
        $router = new Router();
        $router->post('/classes/{id}/remove-teacher/{tid}', 'ClassController@removeTeacher', 'classes.remove_teacher');
        $url = $router->url('classes.remove_teacher', ['id' => 5, 'tid' => 9]);
        $this->assertStringContainsString('/classes/5/remove-teacher/9', $url);
    }
}
