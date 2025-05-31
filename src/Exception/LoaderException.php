<?php

namespace Loader\Exception;

use Exception;

class LoaderException extends Exception
{
    public const UNKNOWN_ERROR = 100;
    public const LOADER_DRIVER_NOT_FOUND_ERROR = 101;
    public const FILE_NOT_FOUND_ERROR = 102;
    public const CLASS_NOT_FOUND_ERROR = 103;
    public const CLASS_OR_FILE_NOT_FOUND_ERROR = 104;
    public const CONFIG_NOT_FOUND_ERROR = 105;
    public const FILE_TYPE_NOT_SUPPORTED_ERROR = 106;
    public const FILE_READ_ERROR = 107;
    public const MAPPER_NOT_FORUND_ERROR = 108;
    public const INVALID_LOAD_CLASS_ERROR = 109;

    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        $code = $code === 0 ? 100 : $code;
        parent::__construct($message, $code, $previous);
    }
}
