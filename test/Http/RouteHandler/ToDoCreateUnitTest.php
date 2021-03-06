<?php declare(strict_types=1);

namespace Acme\ToDo\Http\RouteHandler;

use Acme\ToDo\Model\ToDo;
use Acme\ToDo\Model\ToDoDataMapper;
use DateTimeImmutable;
use League\Route\Http\Exception\BadRequestException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use const Acme\ToDo\DATE_FORMAT;

class ToDoCreateUnitTest extends TestCase
{
    /**
     * @var ToDoCreate
     */
    private $SUT;

    /**
     * @var ToDoDataMapper & MockObject
     */
    private $todoDataMapper;

    protected function setUp(): void
    {
        $this->todoDataMapper = $this->createMock(ToDoDataMapper::class);
        $this->SUT = new ToDoCreate($this->todoDataMapper);
    }

    public function testSuccess(): void
    {
        $data = [
            'name' => 'foo',
            'dueFor' => (new DateTimeImmutable())->format(DATE_FORMAT),
            'doneAt' => (new DateTimeImmutable())->format(DATE_FORMAT),
        ];
        $request = (new ServerRequest())->withParsedBody($data);

        $this->todoDataMapper->expects(once())->method('insert');

        $response = $this->SUT->__invoke($request, []);

        assertSame(201, $response->getStatusCode());
        assertInstanceOf(JsonResponse::class, $response); /** @var JsonResponse $response */
        assertInstanceOf(ToDo::class, $response->getPayload());
        /** @var ToDo $item */
        $item = $response->getPayload();
        assertSame($data['name'], $item->getName());
        assertSame($data['dueFor'], $item->getDueForAsString());
        assertSame($data['doneAt'], $item->getDoneAtAsString());
    }

    public function testInvalidRequestBody(): void
    {
        $request = new ServerRequest();

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Invalid request body');

        $this->SUT->__invoke($request, []);
    }
}
