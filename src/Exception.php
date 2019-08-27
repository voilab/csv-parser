<?php
namespace voilab\csv;

class Exception extends \Exception
{
    /**
     * CSV resource is empty or has headers but no other data
     * @var int
     */
    const EMPTY = 1;

    /**
     * Some headers in [columns] option aren't defined in CSV resource
     * @var int
     */
    const HEADERMISSING = 2;

    /**
     * Some headers in CSV resource already appear once in the resource
     * @var int
     */
    const HEADEREXISTS = 7;

    /**
     * No columns are configured in [columns] option
     * @var int
     */
    const NOCOLUMN = 3;

    /**
     * A line in CSV resource has more columns than the header line
     * @var int
     */
    const DIFFCOLUMNS = 4;

    /**
     * The provided file name doesn't exist
     * @var int
     */
    const NOFILE = 5;

    /**
     * The provided data is not of type [resource]
     * @var int
     */
    const NORESOURCE = 6;

    /**
     * Resource has no header line and is not seekable
     * @var int
     */
    const NOTSEEKABLE = 7;
}
