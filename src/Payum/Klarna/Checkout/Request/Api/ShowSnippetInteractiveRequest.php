<?php
namespace Payum\Klarna\Checkout\Request\Api;

use Payum\Core\Request\BaseInteractiveRequest;

class ShowSnippetInteractiveRequest extends BaseInteractiveRequest
{
    /**
     * @var string
     */
    protected $snippet;

    /**
     * @param string $snippet
     */
    public function __construct($snippet)
    {
        $this->snippet = $snippet;
    }

    /**
     * @return string
     */
    public function getSnippet()
    {
        return $this->snippet;
    }
} 