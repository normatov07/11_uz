<?php defined('SYSPATH') or die('No direct script access.');

class Mailer
{
    public $to;
    public $from;
    public $reply_to;
    public $cc;
    public $bcc;
    public $subject;
    public $body;
    public $headers;

    public $type;

    public $charset;

    public $files;

    public $mime_type;

    public $has_attach;
    public $boundary;

    function __construct()
    {
        $this->headers = array();

        $this->type = 'text/plain';
        $this->charset = 'utf-8';

        $this->files = array();
        $this->mime_type = 'application/octet-stream';

        $this->has_attach = false;

        if (!defined('CRLF'))
        {
            define('CRLF', PHP_EOL);
        }
    }

    public function addFile($name, $type, $body)
    {
        $this->files[] = array('name' => $name, 'type' => $type, 'body' => $body);
    }

    public function buildHeaders()
    {
        if (!empty($this->from))
        {
            $this->headers[] = 'From: ' . $this->from . CRLF;
        }

        if (!empty($this->to))
        {
            $this->headers[] = 'Reply-to: ' . $this->reply_to . CRLF;
        }
    }

    public function buildMimeHeaders()
    {
        $this->headers[] = "MIME-Version: 1.0" . CRLF;

        if ($this->has_attach)
        {
            $this->boundary = md5(uniqid(time()));
            $this->headers[] = 'Content-Type: multipart/mixed; boundary="' . $this->boundary . '"' . CRLF;
            $this->headers[] = "This is a multi-part message in MIME format" . CRLF;
            $this->headers[] = "--" . $this->boundary . CRLF;
        }

        $this->headers[] = 'Content-type:'. $this->type .'; charset=' . $this->charset . CRLF;
        $this->headers[] = 'Content-Transfer-Encoding: 7bit' . CRLF . CRLF;
    }

    public function buildBodyParts()
    {
        $body_parts = array();

        if (!$this->has_attach)
        {
            return true;
        }

        $body_parts[0] = $this->body . CRLF . CRLF;

        for ($i = 0; $i < count($this->files); $i++)
        {
            $file = $this->files[$i];
            $file_body = chunk_split(base64_encode($file['body']), 76, CRLF);
            $body_parts[$i + 1] = "--" . $this->boundary . CRLF;

            if (!empty($file['type']))
            {
                $this->mime_type = $file['type'];
            }

            $body_parts[$i + 1] .= 'Content-Type: ' . $this->mime_type . '; name="' . basename($file['name']) . '"' . CRLF ;
            $body_parts[$i + 1] .= "Content-Transfer-Encoding: base64" . CRLF . CRLF;
            $body_parts[$i + 1] .=  $file_body . CRLF . CRLF;
        }
        if(empty($body_parts[$i + 1])) $body_parts[$i + 1] = '';
        $body_parts[$i + 1] .= "--" . $this->boundary . "--";

        $this->body = implode('', $body_parts);
        return true;
    }

    public function validateMail($str) {
        return str_replace(array('\r\r', '\r\0', '\r\n\r\n', '\n\n', '\n\0', PHP_EOL . PHP_EOL), '', $str);
    }

    public function send()
    {
        if (count($this->files) > 0)
        {
            $this->has_attach = true;
        }

        $this->buildHeaders();
        $this->buildMimeHeaders();

        if (!$this->buildBodyParts())
        {
            return false;
        }

        $headers = join("", $this->headers);
        //$headers = str_replace("\r\r", "\r", $headers);
        //$headers = str_replace("\r\r", "\r");

        //$file = 'mail.txt';
        //file_put_contents($file, $headers . $this->body);

        return @mail($this->to, stripslashes(trim($this->subject)), stripslashes($this->body), $headers);
    }
}