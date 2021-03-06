<?php

/**
 * Tine 2.0
 *
 * @package     Tinebase
 * @subpackage  Server
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @copyright   Copyright (c) 2018 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Cornelius Weiß <c.weiss@metaways.de>
 */

/**
 * Class Tinebase_Http_Request
 *
 * NOTE: ^%*&* Zend\Http\PhpEnvironment\Request can't cope with input streams
 *       which leads to waste of mem e.g. on large file upload via PUT (WebDAV)
 */
class Tinebase_Http_Request extends Zend\Http\PhpEnvironment\Request
{
    protected $_inputStream;

    public function getContentStream($rewind = true)
    {
        if (! $this->_inputStream) {
            if (! empty($this->content)) {
                $this->_inputStream = fopen('php://temp', 'r+');
                fputs($this->_inputStream, $this->content);
            } else {
                // NOTE: as of php 5.6 php://input can be rewinded ... but for POST only and this is even SAPI dependend
                $this->_inputStream = fopen('php://temp', 'r+');
                stream_copy_to_stream(fopen('php://input', 'r'), $this->_inputStream);
            }
        }

        if ($rewind) {
            rewind($this->_inputStream);
        }

        return $this->_inputStream;
    }

    /**
     * Get raw request body
     *
     * @return string
     */
    public function getContent()
    {
        if (empty($this->content)) {
            $requestBody = stream_get_contents($this->getContentStream(true));
            rewind($this->_inputStream);
            if (strlen($requestBody) > 0) {
                $this->content = $requestBody;
            }
        }

        return $this->content;
    }


}