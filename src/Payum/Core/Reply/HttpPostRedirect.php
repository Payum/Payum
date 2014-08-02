<?php
namespace Payum\Core\Reply;

class HttpPostRedirect extends HttpResponse
{
    /**
     * @param string $content
     * @param array $fields
     */
    public function __construct($content, array $fields = array())
    {
        parent::__construct($this->prepareContent($content, $fields));
    }

    /**
     * @param $url
     * @param array $fields
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
            <p>%2$s<input type="submit" value="Continue" /></p>
        </form>
    </body>
</html>
HTML;

       return sprintf($content, htmlspecialchars($url, ENT_QUOTES, 'UTF-8'), $formInputs);
    }
}