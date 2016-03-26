<?php

use FastSimpleHTMLDom\HtmlDomParser;

class NodeListTest extends PHPUnit_Framework_TestCase
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
     * @dataProvider findTests
     */
    public function testFind($html, $selector, $count)
    {
        $document = new HtmlDomParser($html);
        $nodeList =$document->find('section');

        $elements = $nodeList->find($selector);

        $this->assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDomNode', $elements);
        $this->assertEquals($count, count($elements));

        foreach ($elements as $node) {
            $this->assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDom', $node);
        }
    }

    public function findTests()
    {
        $html = $this->loadFixture('testpage.html');
        return array(
            array($html, '.fake h2', 0),
            array($html, 'article', 16),
            array($html, '.radio', 3),
            array($html, 'input.radio', 3),
            array($html, 'ul li', 9),
            array($html, 'fieldset#forms__checkbox li, fieldset#forms__radio li', 6),
            array($html, 'input[id]', 23),
            array($html, 'input[id=in]', 1),
            array($html, '#in', 1),
            array($html, '*[id]', 51),
            array($html, 'text', 390),
        );
    }

    public function testInnerHtml()
    {
        $html = '<div><p>foo</p><p>bar</p></div>';
        $document = new HtmlDomParser($html);
        $element = $document->find('p');

        $this->assertEquals('<p>foo</p><p>bar</p>', $element->innerHtml());
        $this->assertEquals('<p>foo</p><p>bar</p>', $element->innertext);
    }

    public function testText()
    {
        $html = '<div><p>foo</p><p>bar</p></div>';
        $document = new HtmlDomParser($html);
        $element = $document->find('p');

        $this->assertEquals('foobar', $element->text());
        $this->assertEquals('foobar', $element->plaintext);
    }
}
