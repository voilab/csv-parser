<?php
namespace voilab\csv;

interface I18nInterface {

    /**
     * Returns a string based on the key argument. Values for [key] are the
     * constant names of Exceptions
     *
     * @param string $key a constant name from Exception
     * @return string the translated string
     */
    public function t($key);
}
