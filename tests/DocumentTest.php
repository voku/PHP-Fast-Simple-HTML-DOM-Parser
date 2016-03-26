<?php

use FastSimpleHTMLDom\HtmlDomParser;
use FastSimpleHTMLDom\SimpleHtmlDom;

class DocumentTest extends PHPUnit_Framework_TestCase
{
    protected function loadFixture($filename)
    {
        $path = __DIR__ . '/fixtures/' . $filename;
        if (file_exists($path)) {
            return file_get_contents($path);
        }

        return null;
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructWithInvalidArgument()
    {
        new HtmlDomParser(array('foo'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testLoadHtmlWithInvalidArgument()
    {
        $document = new HtmlDomParser();
        $document->loadHtml(array('foo'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testLoadWithInvalidArgument()
    {
        $document = new HtmlDomParser();
        $document->load(array('foo'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testLoadHtmlFileWithInvalidArgument()
    {
        $document = new HtmlDomParser();
        $document->loadHtmlFile(array('foo'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testLoad_fileWithInvalidArgument()
    {
        $document = new HtmlDomParser();
        $document->load_file(array('foo'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testLoadHtmlFileWithNotExistingFile()
    {
        $document = new HtmlDomParser();
        $document->loadHtmlFile('/path/to/file');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testLoadHtmlFileWithNotLoadFile()
    {
        $document = new HtmlDomParser();
        $document->loadHtmlFile('http://fobar');
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testMethodNotExist()
    {
        $document = new HtmlDomParser();
        $document->bar();
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testStaticMethodNotExist()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        HtmlDomParser::bar();
    }

    public function testNotExistProperty()
    {
        $document = new HtmlDomParser();

        self::assertNull($document->foo);
    }

    public function testConstruct()
    {
        $html = '<div>foo</div>';
        $document = new HtmlDomParser($html);

        $element = new SimpleHtmlDom($document->getDocument()->documentElement);

        self::assertEquals($html, $element->outertext);
    }

    public function testLoadHtmlFile()
    {
        $file = __DIR__ . '/fixtures/testpage.html';
        $document = new HtmlDomParser();

        $document->loadHtmlFile($file);
        self::assertNotNull(count($document('div')));

        $document->load_file($file);
        self::assertNotNull(count($document('div')));

        $document = HtmlDomParser::file_get_html($file);
        self::assertNotNull(count($document('div')));
    }

    public function testLoadHtml()
    {
        $html = $this->loadFixture('testpage.html');
        $document = new HtmlDomParser();

        $document->loadHtml($html);
        self::assertNotNull(count($document('div')));

        $document->load($html);
        self::assertNotNull(count($document('div')));

        $document = HtmlDomParser::str_get_html($html);
        self::assertNotNull(count($document('div')));
    }

    public function testGetDocument()
    {
        $document = new HtmlDomParser();
        self::assertInstanceOf('DOMDocument', $document->getDocument());
    }

    /**
     * @dataProvider findTests
     */
    public function testFind($html, $selector, $count)
    {
        $document = new HtmlDomParser($html);
        $elements = $document->find($selector);

        self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDomNode', $elements);
        self::assertEquals($count, count($elements));

        foreach ($elements as $element) {
            self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDom', $element);
        }

        if ($count !== 0) {
            $element = $document->find($selector, -1);
            self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDom', $element);
        }
    }

    public function findTests()
    {
        $html = $this->loadFixture('testpage.html');

        return array(
            [$html, '.fake h2', 0],
            [$html, 'article', 16],
            [$html, '.radio', 3],
            [$html, 'input.radio', 3],
            [$html, 'ul li', 35],
            [$html, 'fieldset#forms__checkbox li, fieldset#forms__radio li', 6],
            [$html, 'input[id]', 23],
            [$html, 'input[id=in]', 1],
            [$html, '#in', 1],
            [$html, '*[id]', 52],
            [$html, 'text', 462],
            [$html, 'comment', 3],
        );
    }

    public function testHtml()
    {
        $html = $this->loadFixture('testpage.html');
        $document = new HtmlDomParser($html);

        self::assertTrue(is_string($document->html()));
        self::assertTrue(is_string($document->outertext));
        self::assertTrue(strlen($document) > 0);


        $html = '<div>foo</div>';
        $document = new HtmlDomParser($html);

        self::assertEquals($html, $document->html());
        self::assertEquals($html, $document->outertext);
        self::assertEquals($html, $document);
    }

    public function testInnerHtml()
    {
        $html = '<div><div>foo</div></div>';
        $document = new HtmlDomParser($html);

        self::assertEquals('<div>foo</div>', $document->innerHtml());
        self::assertEquals('<div>foo</div>', $document->innertext());
        self::assertEquals('<div>foo</div>', $document->innertext);
    }

    public function testText()
    {
        $html = '<div>foo</div>';
        $document = new HtmlDomParser($html);

        self::assertEquals('foo', $document->text());
        self::assertEquals('foo', $document->plaintext);
    }

    public function testSave()
    {
        $html = $this->loadFixture('testpage.html');
        $document = new HtmlDomParser($html);

        self::assertTrue(is_string($document->save()));
    }

    public function testClear()
    {
        $document = new HtmlDomParser();

        self::assertNull($document->clear());
    }
}
