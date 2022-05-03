<?php
namespace Payum\Core\Reply;

class HttpPostRedirect extends HttpResponse
{
    protected string $url;

    protected array $fields;

    /**
     * @param string[] $headers
     */
    public function __construct(string $url, array $fields = [], $statusCode = 200, array $headers = [])
    {
        $this->url = $url;
        $this->fields = $fields;

        parent::__construct($this->prepareContent($url, $fields), $statusCode, $headers);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    protected function prepareContent(string $url, array $fields): string
    {
        $formInputs = '';
        foreach ($fields as $name => $value) {
            $formInputs .= sprintf(
                '<input type="hidden" name="%1$s" value="%2$s" />',
                htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            )."\n";
        }

        $content = <<<'HTML'
<!DOCTYPE html>
<html>
    <head>
        <title>Redirecting...</title>
    </head>
    <body onload="document.forms[0].submit();">
        <form action="%1$s" method="post">
            <p>Redirecting to payment page...</p>
            <p>%2$s</p>
        </form>
    </body>
</html>
HTML;

        return sprintf($content, htmlspecialchars($url, ENT_QUOTES, 'UTF-8'), $formInputs);
    }
}
