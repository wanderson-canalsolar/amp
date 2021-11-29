<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Application;

defined('ABSPATH') || die();
global $wp_filesystem;

/**
 * Class WpfdHelperDocument
 */
class WpfdHelperDocument
{
    /**
     * File path
     *
     * @var string
     */
    private $filePath;
    /**
     * File id
     *
     * @var integer
     */
    private $fileId;
    /**
     * File Object
     *
     * @var array|null|WP_Post
     */
    private $file;
    /**
     * File type
     *
     * @var string
     */
    private $fileType;
    /**
     * File mime allow to parse
     *
     * @var array
     */
    private $fileMimes = array(
        'pdf'        => array(
            'application/pdf',
        ),
        'text'       => array(
            'text/plain',
            'text/csv',
            'text/tab-separated-values',
            'text/calendar',
            'text/css',
            'text/html',
        ),
        'richtext'   => array(
            'text/richtext',
            'application/rtf',
        ),
        'doc'        => array(
            'application/msword',
        ),
        'docx'       => array(
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-word.document.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'application/vnd.ms-word.template.macroEnabled.12',
            'application/vnd.oasis.opendocument.text',
        ),
        'xls'        => array(
            'application/vnd.ms-excel',
        ),
        'excel'      => array(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel.sheet.macroEnabled.12',
            'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'application/vnd.ms-excel.template.macroEnabled.12',
            'application/vnd.ms-excel.addin.macroEnabled.12',
            'application/vnd.oasis.opendocument.spreadsheet',
            'application/vnd.oasis.opendocument.chart',
            'application/vnd.oasis.opendocument.database',
            'application/vnd.oasis.opendocument.formula',
        ),
        'powerpoint' => array(
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.presentationml.template',
            'application/vnd.ms-powerpoint.template.macroEnabled.12',
            'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'application/vnd.ms-powerpoint.slide.macroEnabled.12',
            'application/vnd.oasis.opendocument.presentation',
            'application/vnd.oasis.opendocument.graphics',
        ),
    );

    /**
     * Class constructor.
     *
     * @param integer $postid   Post id
     * @param string  $filepath File path
     *
     * @return void
     */
    public function __construct($postid, $filepath)
    {
        $this->fileId   = absint($postid);
        $this->filePath = $filepath;
        $this->file     = get_post($postid);
        $this->fileType = $this->file->post_mime_type;
    }

    /**
     * Get content from the file
     *
     * @return string
     */
    public function getContent()
    {
        // phpcs:disable Generic.PHP.NoSilencedErrors.Discouraged -- Disable unwant error in content
        @ini_set('memory_limit', '-1');
        @ini_set('max_execution_time', '0');
        @set_time_limit(0);
        // phpcs:enable
        $content   = false;
        $mimeGroup = $this->getMimeGroup();

        if (!$mimeGroup) {
            return '';
        }

        $encodes = 'UTF-8, ASCII,';
        $encodes .= 'ISO-8859-1, ISO-8859-2, ISO-8859-3, ISO-8859-4, ISO-8859-5,';
        $encodes .= 'ISO-8859-6, ISO-8859-7, ISO-8859-8, ISO-8859-9, ISO-8859-10,';
        $encodes .= 'ISO-8859-13, ISO-8859-14, ISO-8859-15, ISO-8859-16,';
        $encodes .= 'Windows-1251, Windows-1252, Windows-1254';

        switch ($mimeGroup) {
            case 'pdf':
                $content = $this->getPdfContent();
                break;
            case 'text':
                $content = $this->getTxtContent();
                break;
            case 'richtext':
                $content = $this->getRichTextContent();
                break;
            case 'doc':
                $content = $this->getDocContent();
                break;
            case 'docx':
                $content = $this->getDocxContent();
                break;
            case 'xls':
                $content = ''; //$this->getXlsContent();
                break;
            case 'excel':
                $content = $this->getExcelContent();
                break;
            case 'powerpoint':
                $content = $this->getPowerPointContent();
                break;
            default:
                break;
        }
        $content = $this->cleanChar($content);

        switch (mb_detect_encoding($content, $encodes)) {
            case 'ISO-8859-1':
                return utf8_encode($content);
            default:
                return mb_convert_encoding($content, 'UTF-8');
        }
    }

    /**
     * Get Pdf content
     *
     * @return string
     */
    private function getPdfContent()
    {
        if (!file_exists($this->filePath)) {
            return '';
        }
        $application = Application::getInstance('Wpfd');
        $pdfContent  = '';
        // pdfparser run only php 5.3+
        if (version_compare(PHP_VERSION, '5.3', '>=')) {
            include_once($application->getPath() . '/admin/classes/pdfParserLoader.php');
            // a wrapper class was conditionally included if we're running PHP 5.3+ so let's try that
            if (class_exists('wpfdPdfParser')) {
                include_once($application->getPath() . '/admin/classes/pdfparser/autoload.php');
                // try PdfParser first
                $parser = new WpfdPdfParser();
                $parser = $parser->init();

                try {
                    $pdf        = $parser->parseFile($this->filePath);
                    $pdfContent = $pdf->getText();
                } catch (Exception $e) {
                    $pdfContent = '';
                }
            }
        }

        // PDF2Text
        if ($pdfContent === '') {
            if (!class_exists('PDF2Text')) {
                include_once($application->getPath() . '/admin/classes/class.pdf2text.php');
            }

            $pdfParser = new PDF2Text();
            $pdfParser->setFilename($this->filePath);
            $pdfParser->decodePDF();
            $pdfContent = $pdfParser->output();
        }

        $pdfContent  = mb_convert_encoding($pdfContent, 'UTF-8');

        return $pdfContent;
    }

    /**
     * Get doc content for Office 2003 format
     * Not working well with utf8
     *
     * @return string
     */
    private function getDocContent()
    {

        if (!file_exists($this->filePath)) {
            return '';
        }
        // TODO: Decode file for content, this work fine with all latin char BUT NOT working well with utf8.
        // Need sonething else better!!
        $fh = fopen($this->filePath, 'r');
        if ($fh !== false) {
            $headers = fread($fh, 0xA00);
            $n1      = (ord($headers[0x21C]) - 1); // 1 = (ord(n)*1) ; Document has from 0 to 255 characters
            $n2      = ((ord($headers[0x21D]) - 8) * 256); // 1 = ((ord(n)-8)*256) ; Document has from 256 to 63743 characters
            // 1 = ((ord(n)*256)*256) ; Document has from 63744 to 16775423 characters
            $n3 = ((ord($headers[0x21E]) * 256) * 256);
            // 1 = (((ord(n)*256)*256)*256) ; Document has from 16775424 to 4294965504 characters
            $n4                 = (((ord($headers[0x21F]) * 256) * 256) * 256);
            $textLength         = ($n1 + $n2 + $n3 + $n4); // Total length of text in the document
            $extractedPlaintext = fread($fh, $textLength);
            $extractedPlaintext = preg_replace_callback(
                "/(\\\\x..)/isU",
                function ($m) {
                    return chr(hexdec(substr($m[0], 2)));
                },
                $extractedPlaintext
            );
            $doccontent         = $extractedPlaintext;
        } else {
            $doccontent = '';
        }

        // try with Filetotext
        if ($doccontent === '') {
            if (!class_exists('Filetotext')) {
                $application = Application::getInstance('Wpfd');
                include_once($application->getPath() . '/site/helpers/class.filetotext.php');
            }
            $handle     = new Filetotext($this->filePath);
            $doccontent = $handle->convertToText();
        }

        return $doccontent;
    }

    /**
     * Get docx content
     *
     * @return string
     */
    private function getDocxContent()
    {
        if (strpos($this->fileType, 'opendocument') !== false) {
            $content = $this->getContentFromArchive('content.xml');
        } else {
            $content = $this->getContentFromArchive('word/document.xml');
        }

        return $content;
    }

    /**
     * Get xls content for Office 2003 format
     *
     * Not working well
     *
     * @return string
     */
    private function getXlsContent()
    {
        if (!file_exists($this->filePath)) {
            return '';
        }
        if (!class_exists('Spreadsheet_Excel_Reader')) {
            $application = Application::getInstance('Wpfd');
            include_once($application->getPath() . '/site/helpers/class.exceltotext.php');
        }
        $row_numbers = false;
        $col_letters = true;
        $sheet       = 0;
        $table_class = 'excel';
        $handle      = new Spreadsheet_Excel_Reader($this->filePath);
        $content     = $handle->dump($row_numbers, $col_letters, $sheet, $table_class);
        $content     = mb_convert_encoding($content, 'UTF-8');
        $content     = html_entity_decode($content);

        return $this->cleanXmlContent($content);
    }

    /**
     * Get excel content
     *
     * @return string
     */
    private function getExcelContent()
    {
        if (strpos($this->fileType, 'opendocument') !== false) {
            $content = $this->getContentFromArchive('content.xml');
        } else {
            $content = $this->getContentFromArchive('xl/sharedStrings.xml'); // this is stored in the .docx zip
        }

        return $content;
    }

    /**
     * Check plain text
     *
     * @param string $s String to check is plain text or not
     *
     * @return boolean
     */
    private function rtfIsPlainText($s)
    {
        $arrfailAt = array('*', 'fonttbl', 'colortbl', 'datastore', 'themedata');
        $total = count($arrfailAt);
        for ($i = 0; $i < $total; $i++) {
            if (!empty($s[$arrfailAt[$i]])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get rich text content
     *
     * @return mixed|null|string|string[]
     */
    private function getRichTextContent()
    {
        if (!file_exists($this->filePath)) {
            return '';
        }
        // Read the data from the input file.
        $text = file_get_contents($this->filePath);
        if (!strlen($text)) {
            return '';
        }
        // Create empty stack array.
        $document = '';
        $stack    = array();
        $j        = -1;
        // Read the data character-by- character…
        $len = strlen($text);
        for ($i = 0, $len; $i < $len; $i++) {
            $c = $text[$i];

            // Depending on current character select the further actions.
            switch ($c) {
                // the most important key word backslash
                case '\\':
                    // read next character
                    $nc = $text[$i + 1];

                    // If it is another backslash or nonbreaking space or hyphen,
                    // then the character is plain text and add it to the output stream.
                    if ($nc === '\\' && $this->rtfIsPlainText($stack[$j])) {
                        $document .= '\\';
                    } elseif ($nc === '~' && $this->rtfIsPlainText($stack[$j])) {
                        $document .= ' ';
                    } elseif ($nc === '_' && $this->rtfIsPlainText($stack[$j])) {
                        $document .= '-';
                    } elseif ($nc === '*') {
                        $stack[$j]['*'] = true;
                    } elseif ($nc === "'") {
                        $hex = substr($text, $i + 2, 2);
                        if ($this->rtfIsPlainText($stack[$j])) {
                            $document .= html_entity_decode('&#' . hexdec($hex) . ';');
                        }
                        //Shift the pointer.
                        $i += 2;
                        // Since, we’ve found the alphabetic character, the next characters are control word
                        // and, possibly, some digit parameter.
                    } elseif ($nc >= 'a' && $nc <= 'z' || $nc >= 'A' && $nc <= 'Z') {
                        $word  = '';
                        $param = null;

                        // Start reading characters after the backslash.
                        $textlength = strlen($text);
                        $m = 0;
                        for ($k = $i + 1, $m; $k < $textlength; $k++, $m++) {
                            $nc = $text[$k];
                            // If the current character is a letter and there were no digits before it,
                            // then we’re still reading the control word. If there were digits, we should stop
                            // since we reach the end of the control word.
                            if ($nc >= 'a' && $nc <= 'z' || $nc >= 'A' && $nc <= 'Z') {
                                if (empty($param)) {
                                    $word .= $nc;
                                } else {
                                    break;
                                }
                                // If it is a digit, store the parameter.
                            } elseif ($nc >= '0' && $nc <= '9') {
                                $param .= $nc;
                            } elseif ($nc === '-') {
                                // Since minus sign may occur only before a digit parameter, check whether
                                // $param is empty. Otherwise, we reach the end of the control word.
                                if (empty($param)) {
                                    $param .= $nc;
                                } else {
                                    break;
                                }
                            } else {
                                break;
                            }
                        }
                        // Shift the pointer on the number of read characters.
                        $i += $m - 1;

                        // Start analyzing what we’ve read. We are interested mostly in control words.
                        $toText = '';
                        switch (strtolower($word)) {
                            // If the control word is "u", then its parameter is the decimal notation of the
                            // Unicode character that should be added to the output stream.
                            // We need to check whether the stack contains \ucN control word. If it does,
                            // we should remove the N characters from the output stream.
                            case 'u':
                                $toText  .= html_entity_decode('&#x' . dechex($param) . ';');
                                // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- I don't want any error on content
                                $ucDelta = @$stack[$j]['uc'];
                                if ($ucDelta > 0) {
                                    $i += $ucDelta;
                                }
                                break;
                            // Select line feeds, spaces and tabs.
                            case 'par':
                            case 'page':
                            case 'column':
                            case 'line':
                            case 'lbr':
                                $toText .= "\n";
                                break;
                            case 'emspace':
                            case 'enspace':
                            case 'qmspace':
                                $toText .= ' ';
                                break;
                            case 'tab':
                                $toText .= "\t";
                                break;
                            // Add current date and time instead of corresponding labels.
                            case 'chdate':
                                $toText .= date('m.d.Y');
                                break;
                            case 'chdpl':
                                $toText .= date('l, j F Y');
                                break;
                            case 'chdpa':
                                $toText .= date('D, j M Y');
                                break;
                            case 'chtime':
                                $toText .= date('H:i:s');
                                break;
                            // Replace some reserved characters to their html analogs.
                            case 'emdash':
                                $toText .= html_entity_decode('&mdash;');
                                break;
                            case 'endash':
                                $toText .= html_entity_decode('&ndash;');
                                break;
                            case 'bullet':
                                $toText .= html_entity_decode('&#149;');
                                break;
                            case 'lquote':
                                $toText .= html_entity_decode('&lsquo;');
                                break;
                            case 'rquote':
                                $toText .= html_entity_decode('&rsquo;');
                                break;
                            case 'ldblquote':
                                $toText .= html_entity_decode('&laquo;');
                                break;
                            case 'rdblquote':
                                $toText .= html_entity_decode('&raquo;');
                                break;
                            // Add all other to the control words stack. If a control word
                            // does not include parameters, set &param to true.
                            default:
                                $stack[$j][strtolower($word)] = empty($param) ? true : $param;
                                break;
                        }
                        // Add data to the output stream if required.
                        if ($this->rtfIsPlainText($stack[$j])) {
                            $document .= $toText;
                        }
                    }

                    $i++;
                    break;
                // If we read the opening brace {, then new subgroup starts and we add
                // new array stack element and write the data from previous stack element to it.
                case '{':
                    // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- I don't want any error on content
                    @array_push($stack, $stack[$j++]);
                    break;
                // If we read the closing brace }, then we reach the end of subgroup and should remove
                // the last stack element.
                case '}':
                    array_pop($stack);
                    $j--;
                    break;
                // Skip “trash”.
                case '\0':
                case '\r':
                case '\f':
                case '\n':
                    break;
                // Add other data to the output stream if required.
                default:
                    // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- I don't want any error on content
                    if (@$this->rtfIsPlainText($stack[$j])) {
                        $document .= $c;
                    }
                    break;
            }
        }

        // Return result.
        return $this->cleanChar($document);
    }

    /**
     * Get txt content
     *
     * @return string
     */
    private function getTxtContent()
    {
        global $wp_filesystem;
        $content = '';
        if (!file_exists($this->filePath)) {
            return '';
        }
        WP_Filesystem();
        if (!method_exists($wp_filesystem, 'exists') || !method_exists($wp_filesystem, 'get_contents')) {
            return '';
        }
        $content = $wp_filesystem->exists($this->filePath) ? $wp_filesystem->get_contents($this->filePath) : '';
        if ($content === '') {
            try {
                $handle  = fopen($this->filePath, 'r');
                $content = fread($handle, filesize($this->filePath));
                fclose($handle);
            } catch (Exception $e) {
                return '';
            }
        }

        return sanitize_text_field($content);
    }

    /**
     * Get power point content
     *
     * @return string
     */
    private function getPowerPointContent()
    {
        if (!class_exists('ZipArchive')) {
            return '';
        }

        $output = '';
        $zip    = new ZipArchive();

        if (strpos($this->fileType, 'opendocument') !== false) {
            $output = $this->getContentFromArchive('content.xml');
        } else {
            if (true === $zip->open($this->filePath)) {
                $slideNum = 1; // Loop through each slide archive
                // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found -- $index base on $slideNum
                while (false !== ($index = $zip->locateName('ppt/slides/slide' . absint($slideNum) . '.xml'))) {
                    $data   = $zip->getFromIndex($index);
                    $output .= ' ' . $this->getXmlContent($data);
                    $slideNum++;
                }
                $zip->close();
            }
        }

        return sanitize_text_field($output);
    }

    /**
     * Get mime group
     *
     * @return boolean|integer|string
     */
    private function getMimeGroup()
    {
        foreach ($this->fileMimes as $mimeGroup => $mimeTypes) {
            if (in_array($this->fileType, $mimeTypes, true)) {
                return $mimeGroup;
                //break;
            }
        }

        return false;
    }

    /**
     * Get content from archive
     *
     * @param string $xmlfilename Xml file name
     *
     * @return string
     */
    private function getContentFromArchive($xmlfilename)
    {
        if (!class_exists('ZipArchive')) {
            return '';
        }

        $output = '';
        $zip    = new ZipArchive();

        if ($zip->open($this->filePath) === true) {
            $index = $zip->locatename($xmlfilename);

            if ($index !== false) {
                $data   = $zip->getFromIndex($index);
                $output = $this->getXmlContent($data);
            }
            $zip->close();
        }

        return sanitize_text_field($output);
    }

    /**
     * Get xml content
     *
     * @param string $data Xml context
     *
     * @return mixed|null|string|string[]
     */
    private function getXmlContent($data = '')
    {
        if (!class_exists('DOMDocument')) {
            return '';
        }
        $xml = new DOMDocument();
        $xml->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);

        return $this->cleanXmlContent($xml->saveXML());
    }

    /**
     * Clean Xml content
     *
     * @param string $content Content to clean
     *
     * @return mixed|null|string|string[]
     */
    private function cleanXmlContent($content = '')
    {
        if ($content === '') {
            return '';
        }

        if (function_exists('mb_convert_encoding')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        }

        $acceptAtt = array(
            'a'     => array('title'),
            'img'   => array('alt', 'src', 'longdesc', 'title'),
            'input' => array('placeholder', 'value'),
        );

        $content   = trim($content);
        $contenAtt = array();
        if (!empty($acceptAtt) && !empty($content) && is_array($acceptAtt) &&
            class_exists('DOMDocument') && function_exists('libxml_use_internal_errors')) {
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML($content);

            foreach ($acceptAtt as $tag => $attributes) {
                $node_list = $dom->getElementsByTagName($tag);
                for ($i = 0; $i < $node_list->length; $i++) {
                    $node = $node_list->item($i);
                    if ($node->hasAttributes()) {
                        foreach ($node->attributes as $attr) {
                            if (isset($attr->name) && in_array($attr->name, $attributes, true)) {
                                $contenAtt[] = sanitize_text_field($attr->nodeValue);
                            }
                        }
                    }
                }
            }
        }

        if (!empty($contenAtt)) {
            $content .= ' ' . implode(' ', $contenAtt);
        }

        return $this->cleanChar($content);
    }

    /**
     * Clean char
     *
     * @param string $content Content to clean
     *
     * @return mixed|null|string|string[]
     */
    private function cleanChar($content = '')
    {
        if ($content === '') {
            return '';
        }
        $content = preg_replace('/<[^>]*>/', ' \\0 ', $content);
        $content = preg_replace('/&nbsp;/', ' ', $content);
        $content = str_replace(array('<br />', '<br/>', '<br>'), ' ', $content);
//        $content = strip_tags($content);
        $content = stripslashes($content);

        $punct   = array(
            '(',
            ')',
            '·',
            "'",
            '´',
            '’',
            '‘',
            '”',
            '“',
            '„',
            '—',
            '–',
            '×',
            '…',
            '€',
            '\n',
            '.',
            ',',
            '/',
            '\\',
            '|',
            '[',
            ']',
            '{',
            '}',
            '•',
            '`'
        );
        $content = str_replace($punct, '', $content);
        $content = preg_replace('/[[:punct:]]/uiU', ' ', $content);
        // Remove more space
        $content = preg_replace('/[[:space:]]/uiU', ' ', $content);
        $content = preg_replace('/\\n|\\R/uiU', ' ', $content);
        $content = sanitize_text_field($content);
        $content = trim($content);

        //$content = html_entity_decode($content);
        return $content;
    }
}
