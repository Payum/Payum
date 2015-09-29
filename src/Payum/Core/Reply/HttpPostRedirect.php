<?php
namespace Payum\Core\Reply;

class HttpPostRedirect extends HttpResponse
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @param string   $url
     * @param array    $fields
     * @param int      $statusCode
     * @param string[] $headers
     */
    public function __construct($url, array $fields = array(), $statusCode = 200, array $headers = array())
    {
        $this->url = $url;
        $this->fields = $fields;

        parent::__construct($this->prepareContent($url, $fields), $statusCode, $headers);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param string $url
     * @param array  $fields
     *
     * @return string
     */
    protected function prepareContent($url, array $fields)
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
