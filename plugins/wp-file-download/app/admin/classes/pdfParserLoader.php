<?php

/**
 * Class WpfdPdfParser
 */
class WpfdPdfParser
{
    /**
     * Call parser
     *
     * @return \Smalot\PdfParser\Parser
     */
    public function init()
    {
        $parser = new \Smalot\PdfParser\Parser();
        return $parser;
    }
}
