<?php

namespace Payum\Core\Request;

class PostRedirectUrlInteractiveRequest extends BaseInteractiveRequest
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var array
     */
    protected $post;

    /**
     * @param string $url
     * @param array $post
     */
    public function __construct($url, array $post = array())
    {
        $this->url = $url;
        $this->post = $post;
    }

    public function getContent()
    {
        $formFields = '';
        foreach ($this->post as $name => $value) {
            $formFields .= sprintf(
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

       return sprintf($content, htmlspecialchars($this->url, ENT_QUOTES, 'UTF-8'), $formFields);
    }
}