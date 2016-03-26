<?php

namespace FastSimpleHTMLDom;


use BadMethodCallException;
use DOMDocument;
use DOMXPath;
use InvalidArgumentException;
use RuntimeException;

/**
 * Class HtmlDomParser
 * 
*@package FastSimpleHTMLDom
 *
 * @property string      outertext Get dom node's outer html
 * @property string      innertext Get dom node's inner html
 * @property-read string plaintext Get dom node's plain text
 *
 * @method string outertext() Get dom node's outer html
 * @method string innertext() Get dom node's inner html
 * @method HtmlDomParser load() load($html) Load HTML from string
 * @method HtmlDomParser load_file() load_file($html) Load HTML from file
 *
 * @method static HtmlDomParser file_get_html() file_get_html($html) Load HTML from file
 * @method static HtmlDomParser str_get_html() str_get_html($html) Load HTML from string
 */
class HtmlDomParser
{
    /**
     * @var DOMDocument
     */
    protected $document;

    /**
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * @var array
     */
    protected $functionAliases = array(
        'outertext' => 'html',
        'innertext' => 'innerHtml',
        'load'      => 'loadHtml',
        'load_file' => 'loadHtmlFile',
    );

    /**
     * @var Callable
     */
    static protected $callback;

    /**
     * Constructor
     *
     * @param string|SimpleHtmlDom $element HTML code or SimpleHtmlDom
     */
    public function __construct($element = null)
    {
        $this->document = new DOMDocument('1.0', $this->getEncoding());

        if ($element instanceof SimpleHtmlDom) {
            $element = $element->getNode();

            $domNode = $this->document->importNode($element, true);
            $this->document->appendChild($domNode);

            return;
        }

        if ($element !== null) {
            $this->loadHtml($element);
        }
    }

    /**
     * Get the encoding to use
     *
     * @return string
     */
    private function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * create DOMDocument from HTML
     *
     * @param string $html
     *
     * @return \DOMDocument
     */
    private function createDOMDocument($html)
    {
        // DOMDocument settings
        $this->document->preserveWhiteSpace = true;
        $this->document->recover = false;
        $this->document->formatOutput = false;

        // set error level
        $internalErrors = libxml_use_internal_errors(true);
        $disableEntityLoader = libxml_disable_entity_loader(true);

        $sxe = simplexml_load_string($html);
        if (libxml_get_errors()) {
            $this->document->loadHTML('<?xml encoding="' . $this->getEncoding() . '">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        } else {
            $this->document = dom_import_simplexml($sxe)->ownerDocument;
        }
        libxml_clear_errors();

        // set encoding
        $this->document->encoding = $this->getEncoding();

        // restore lib-xml settings
        libxml_use_internal_errors($internalErrors);
        libxml_disable_entity_loader($disableEntityLoader);

        return $this->document;
    }

    /**
     * Load HTML from string
     *
     * @param string $html
     *
     * @return HtmlDomParser
     *
     * @throws InvalidArgumentException if argument is not string
     */
    public function loadHtml($html)
    {
        if (!is_string($html)) {
            throw new InvalidArgumentException(__METHOD__ . ' expects parameter 1 to be string.');
        }

        $this->document = $this->createDOMDocument($html);

        return $this;
    }

    /**
     * Load HTML from file
     *
     * @param string $filePath
     *
     * @return HtmlDomParser
     */
    public function loadHtmlFile($filePath)
    {
        if (!is_string($filePath)) {
            throw new InvalidArgumentException(__METHOD__ . ' expects parameter 1 to be string.');
        }

        if (!preg_match("/^https?:\/\//i", $filePath) && !file_exists($filePath)) {
            throw new RuntimeException("File $filePath not found");
        }

        try {
            $html = file_get_contents($filePath);
        } catch (\Exception $e) {
            throw new RuntimeException("Could not load file $filePath");
        }

        if ($html === false) {
            throw new RuntimeException("Could not load file $filePath");
        }

        $this->loadHtml($html);

        return $this;
    }

    /**
     * @return DOMDocument
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Find list of nodes with a CSS selector
     *
     * @param string $selector
     * @param int    $idx
     *
     * @return SimpleHtmlDomNode|SimpleHtmlDom[]
     */
    public function find($selector, $idx = null)
    {
        $xPathQuery = SelectorConverter::toXPath($selector);

        $xPath = new DOMXPath($this->document);
        $nodesList = $xPath->query($xPathQuery);
        $elements = new SimpleHtmlDomNode();

        foreach ($nodesList as $node) {
            $elements[] = new SimpleHtmlDom($node);
        }

        if (is_null($idx)) {
            return $elements;
        } else {
            if ($idx < 0) {
                $idx = count($elements) + $idx;
            }
        }

        if (isset($elements[$idx])) {
            return $elements[$idx];
        } else {
            return new SimpleHtmlDomNodeBlank();
        }
    }

    /**
     * Get dom node's outer html
     *
     * @return string
     */
    public function html()
    {
        if ($this::$callback !== null) {
            call_user_func_array($this::$callback, array($this));
        }

        $content = $this->document->saveHTML($this->document->documentElement);

        return trim($content);
    }

    /**
     * Get dom node's inner html
     *
     * @return string
     */
    public function innerHtml()
    {
        $text = '';

        foreach ($this->document->documentElement->childNodes as $node) {
            $textTmp = trim($this->document->saveXML($node));

            // DEBUG
            //echo $textTmp . "\n";

            $text .= $textTmp;
        }

        return $text;
    }

    /**
     * Get dom node's plain text
     *
     * @return string
     */
    public function text()
    {
        return $this->document->textContent;
    }

    /**
     * Save dom as string
     *
     * @param string $filepath
     *
     * @return string
     */
    public function save($filepath = '')
    {
        $string = $this->innerHtml();
        if ($filepath !== '') {
            file_put_contents($filepath, $string, LOCK_EX);
        }

        return $string;
    }

    /**
     * @param $functionName
     */
    public function set_callback($functionName)
    {
        $this::$callback = $functionName;
    }

    public function clear()
    {
    }

    /**
     * @param $name
     *
     * @return string
     */
    public function __get($name)
    {
        switch ($name) {
            case 'outertext':
                return $this->html();
            case 'innertext':
                return $this->innerHtml();
            case 'plaintext':
                return $this->text();
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->html();
    }

    /**
     * @param string $selector
     * @param int    $idx
     *
     * @return SimpleHtmlDom|SimpleHtmlDomNode|null
     */
    public function __invoke($selector, $idx = null)
    {
        return $this->find($selector, $idx);
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return bool|mixed
     */
    public function __call($name, $arguments)
    {
        if (isset($this->functionAliases[$name])) {
            return call_user_func_array(array($this, $this->functionAliases[$name]), $arguments);
        }
        throw new BadMethodCallException('Method does not exist');
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return HtmlDomParser
     */
    public static function __callStatic($name, $arguments)
    {
        if ($name == 'str_get_html') {
            $parser = new HtmlDomParser();

            return $parser->loadHtml($arguments[0]);
        }

        if ($name == 'file_get_html') {
            $parser = new HtmlDomParser();

            return $parser->loadHtmlFile($arguments[0]);
        }

        throw new BadMethodCallException('Method does not exist');
    }
}
