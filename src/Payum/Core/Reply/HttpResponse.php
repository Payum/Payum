<?php
namespace Payum\Core\Reply;

class HttpResponse extends Base
{
    protected string $content;

    protected int $statusCode;

    /**
     * @var string[]
     */
    protected array $headers;

    /**
     * @param string[] $headers
     */
    public function __construct(string $content, int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
