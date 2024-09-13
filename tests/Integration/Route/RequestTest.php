<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Route;

use Tempest\Database\Id;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\RequestFactory;
use Tempest\Http\Status;
use function Tempest\uri;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Modules\Books\BookController;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
final class RequestTest extends FrameworkIntegrationTestCase
{
    public function test_request_get(): void
    {
        $request = new GenericRequest(
            method: Method::GET,
            uri: '/',
            body: [],
        );

        $this->assertSame('default', $request->get('a', 'default'));

        $request = new GenericRequest(
            method: Method::GET,
            uri: '/?a=1',
            body: [],
        );

        $this->assertSame('1', $request->get('a', 'default'));

        $request = new GenericRequest(
            method: Method::GET,
            uri: '/?a=1',
            body: [
                'a' => '2',
            ],
        );

        $this->assertSame('2', $request->get('a', 'default'));

        $request = new GenericRequest(
            method: Method::GET,
            uri: '/',
            body: [
                'a' => '2',
            ],
        );

        $this->assertSame('2', $request->get('a', 'default'));
    }

    public function test_from_factory(): void
    {
        $_SERVER['REQUEST_METHOD'] = Method::POST->value;
        $_SERVER['REQUEST_URI'] = '/test';
        $_POST = ['test'];
        $_SERVER['HTTP_X-TEST'] = 'test';
        $_COOKIE['test'] = 'test';

        $request = (new RequestFactory())->make();

        $this->assertEquals(Method::POST->value, $request->getMethod());
        $this->assertEquals('/test', $request->getUri()->getPath());
        $this->assertEquals(['test'], $request->getParsedBody());
        $this->assertEquals(['x-test' => ['test']], $request->getHeaders());
        $this->assertEquals(['test' => 'test'], $request->getCookieParams());
    }

    public function test_custom_request_test(): void
    {
        $response = $this->http
            ->post(
                uri: '/create-post',
                body: [
                    'title' => 'test-title',
                    'text' => 'test-text',
                ],
            )
            ->assertOk();

        $this->assertEquals('test-title test-text', $response->getBody());
    }

    public function test_generic_request_can_map_to_custom_request(): void
    {
        $response = $this->http
            ->post(
                uri: '/create-post',
                body: [
                    'title' => 'test-title',
                    'text' => 'test-text',
                ],
            )
            ->assertOk();

        $this->assertEquals('test-title test-text', $response->getBody());
    }

    public function test_custom_request_test_with_validation(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $this->http
            ->post(
                uri: uri([BookController::class, 'store']),
                body: [
                    'title' => 'a',
                ],
            )
            ->assertStatus(Status::FOUND);

        $book = Book::find(new Id(1));
        $this->assertSame(1, $book->id->id);
        $this->assertSame('a', $book->title);
    }

    public function test_custom_request_test_with_nested_validation(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $this->http
            ->post(
                uri: uri([BookController::class, 'storeWithAuthor']),
                body: [
                    'title' => 'a',
                    'author.name' => 'b',
                ],
            )
            ->assertStatus(Status::FOUND);

        $book = Book::find(new Id(1), relations: ['author']);
        $this->assertSame(1, $book->id->id);
        $this->assertSame('a', $book->title);
        $this->assertSame('b', $book->author->name);
    }
}
