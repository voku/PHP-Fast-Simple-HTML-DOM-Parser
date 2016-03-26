<?php

use FastSimpleHTMLDom\HtmlDomParser;
use FastSimpleHTMLDom\SimpleHtmlDom;

class ElementTest extends PHPUnit_Framework_TestCase
{
    protected function loadFixture($filename)
    {
        $path = __DIR__ . '/fixtures/' . $filename;
        if (file_exists($path)) {
            return file_get_contents($path);
        }

        return null;
    }

    public function testConstructor()
    {
        $html = '<input name="username" value="John">User name</input>';

        $document = new HtmlDomParser($html);
        $node = $document->getDocument()->documentElement;

        $element = new SimpleHtmlDom($node);

        self::assertEquals('input', $element->tag);
        self::assertEquals('User name', $element->plaintext);
        self::assertEquals('username', $element->name);
        self::assertEquals('John', $element->value);
    }

    public function testGetNode()
    {
        $html = '<div>foo</div>';

        $document = new HtmlDomParser($html);
        $node = $document->getDocument()->documentElement;
        $element = new SimpleHtmlDom($node);

        self::assertInstanceOf('DOMNode', $element->getNode());
    }

    public function testReplaceNode()
    {
        $html = '<div>foo</div>';
        $replace = '<h1>bar</h1>';

        $document = new HtmlDomParser($html);
        $node = $document->getDocument()->documentElement;
        $element = new SimpleHtmlDom($node);
        $element->outertext = $replace;

        self::assertEquals($replace, $document->outertext);
        self::assertEquals($replace, $element->outertext);

        $element->outertext = '';

        self::assertNotEquals($replace, $document->outertext);
    }

    public function testReplaceChild()
    {
        $html = '<div><p>foo</p></div>';
        $replace = '<h1>bar</h1>';

        $document = new HtmlDomParser($html);
        $node = $document->getDocument()->documentElement;
        $element = new SimpleHtmlDom($node);
        $element->innertext = $replace;

        self::assertEquals('<div><h1>bar</h1></div>', $document->outertext);
        self::assertEquals('<div><h1>bar</h1></div>', $element->outertext);
    }

    public function testGetDom()
    {
        $html = '<div><p>foo</p></div>';

        $document = new HtmlDomParser($html);
        $node = $document->getDocument()->documentElement;
        $element = new SimpleHtmlDom($node);

        self::assertInstanceOf('FastSimpleHTMLDom\HtmlDomParser', $element->getDom());
    }

    /**
     * @dataProvider findTests
     */
    public function testFind($html, $selector, $count)
    {
        $document = new HtmlDomParser($html);
        $element = new SimpleHtmlDom($document->getDocument()->documentElement);

        $elements = $element->find($selector);

        self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDomNode', $elements);
        self::assertEquals($count, count($elements));

        foreach ($elements as $node) {
            self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDom', $node);
        }

        $elements = $element($selector);

        self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDomNode', $elements);
    }

    public function findTests()
    {
        $html = $this->loadFixture('testpage.html');
        return array(
            array($html, '.fake h2', 0),
            array($html, 'article', 16),
            array($html, '.radio', 3),
            array($html, 'input.radio', 3),
            array($html, 'ul li', 35),
            array($html, 'fieldset#forms__checkbox li, fieldset#forms__radio li', 6),
            array($html, 'input[id]', 23),
            array($html, 'input[id=in]', 1),
            array($html, '#in', 1),
            array($html, '*[id]', 52),
            array($html, 'text', 462),
            array($html, 'comment', 3),
        );
    }

    public function testGetElementById()
    {
        $html = $this->loadFixture('testpage.html');

        $document = new HtmlDomParser($html);
        $element = new SimpleHtmlDom($document->getDocument()->documentElement);

        $node = $element->getElementById('in');

        self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDom', $node);
        self::assertEquals('input', $node->tag);
        self::assertEquals('number', $node->type);
        self::assertEquals('5', $node->value);
    }

    public function testGetElementByTagName()
    {
        $html = $this->loadFixture('testpage.html');

        $document = new HtmlDomParser($html);
        $element = new SimpleHtmlDom($document->getDocument()->documentElement);

        $node = $element->getElementByTagName('div');

        self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDom', $node);
        self::assertEquals('div', $node->tag);
        self::assertEquals('top', $node->id);
        self::assertEquals('page', $node->class);
    }

    public function testGetElementsByTagName()
    {
        $html = $this->loadFixture('testpage.html');

        $document = new HtmlDomParser($html);
        $element = new SimpleHtmlDom($document->getDocument()->documentElement);

        $elements = $element->getElementsByTagName('div');

        self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDomNode', $elements);
        self::assertEquals(16, count($elements));

        foreach ($elements as $node) {
            self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDom', $node);
        }
    }

    public function testChildNodes()
    {
        $html = '<div><p>foo</p><p>bar</p></div>';

        $document = new HtmlDomParser($html);
        $element = new SimpleHtmlDom($document->getDocument()->documentElement);

        $nodes = $element->childNodes();

        self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDomNode', $nodes);
        self::assertEquals(2, count($nodes));

        foreach ($nodes as $node) {
            self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDom', $node);
        }

        $node = $element->childNodes(1);

        self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDom', $node);

        self::assertEquals('<p>bar</p>', $node->outertext);
        self::assertEquals('bar', $node->plaintext);

        $node = $element->childNodes(2);
        self::assertNull($node);
    }

    public function testChildren()
    {
        $html = '<div><p>foo</p><p>bar</p></div>';

        $document = new HtmlDomParser($html);
        $element = new SimpleHtmlDom($document->getDocument()->documentElement);

        $nodes = $element->children();

        self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDomNode', $nodes);
        self::assertEquals(2, count($nodes));

        foreach ($nodes as $node) {
            self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDom', $node);
        }

        $node = $element->children(1);

        self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDom', $node);

        self::assertEquals('<p>bar</p>', $node->outertext);
        self::assertEquals('bar', $node->plaintext);
    }

    public function testFirstChild()
    {
        $html = '<div><p>foo</p><p></p></div>';

        $document = new HtmlDomParser($html);
        $element = new SimpleHtmlDom($document->getDocument()->documentElement);

        $node = $element->firstChild();

        self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDom', $node);
        self::assertEquals('<p>foo</p>', $node->outertext);
        self::assertEquals('foo', $node->plaintext);

        $node = $element->lastChild();

        self::assertNull($node->firstChild());
        self::assertNull($node->first_child());
    }

    public function testLastChild()
    {
        $html = '<div><p></p><p>bar</p></div>';

        $document = new HtmlDomParser($html);
        $element = new SimpleHtmlDom($document->getDocument()->documentElement);

        $node = $element->lastChild();

        self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDom', $node);
        self::assertEquals('<p>bar</p>', $node->outertext);
        self::assertEquals('bar', $node->plaintext);

        $node = $element->firstChild();

        self::assertNull($node->lastChild());
        self::assertNull($node->last_child());
    }

    public function testNextSibling()
    {
        $html = '<div><p>foo</p><p>bar</p></div>';

        $document = new HtmlDomParser($html);
        $element = new SimpleHtmlDom($document->getDocument()->documentElement);

        $node = $element->firstChild();
        $sibling = $node->nextSibling();

        self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDom', $sibling);
        self::assertEquals('<p>bar</p>', $sibling->outertext);
        self::assertEquals('bar', $sibling->plaintext);

        $node = $element->lastChild();

        self::assertNull($node->nextSibling());
        self::assertNull($node->next_sibling());
    }

    public function testPreviousSibling()
    {
        $html = '<div><p>foo</p><p>bar</p></div>';

        $document = new HtmlDomParser($html);
        $element = new SimpleHtmlDom($document->getDocument()->documentElement);

        $node = $element->lastChild();
        $sibling = $node->previousSibling();

        self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDom', $sibling);
        self::assertEquals('<p>foo</p>', $sibling->outertext);
        self::assertEquals('foo', $sibling->plaintext);

        $node = $element->firstChild();

        self::assertNull($node->previousSibling());
        self::assertNull($node->prev_sibling());
    }

    public function testParentNode()
    {
        $html = '<div><p>foo</p><p>bar</p></div>';

        $document = new HtmlDomParser($html);
        $element = $document->find('p', 0);

        $node = $element->parentNode();

        self::assertInstanceOf('FastSimpleHTMLDom\SimpleHtmlDom', $node);
        self::assertEquals('div', $node->tag);
        self::assertEquals('div', $element->parent()->tag);
    }

    public function testHtml()
    {
        $html = '<div>foo</div>';
        $document = new HtmlDomParser($html);
        $element = new SimpleHtmlDom($document->getDocument()->documentElement);

        self::assertEquals($html, $element->html());
        self::assertEquals($html, $element->outertext());
        self::assertEquals($html, $element->outertext);
        self::assertEquals($html, (string)$element);
    }

    public function testInnerHtml()
    {
        $html = '<div><div>foo</div></div>';
        $document = new HtmlDomParser($html);
        $element = new SimpleHtmlDom($document->getDocument()->documentElement);

        self::assertEquals('<div>foo</div>', $element->innerHtml());
        self::assertEquals('<div>foo</div>', $element->innertext());
        self::assertEquals('<div>foo</div>', $element->innertext);
    }

    public function testText()
    {
        $html = '<div>foo</div>';
        $document = new HtmlDomParser($html);
        $element = new SimpleHtmlDom($document->getDocument()->documentElement);

        self::assertEquals('foo', $element->text());
        self::assertEquals('foo', $element->plaintext);
    }

    public function testGetAllAttributes()
    {
        $attr = array('class' => 'post', 'id' => 'p1');
        $html = '<html><div class="post" id="p1">foo</div><div>bar</div></html>';

        $document = new HtmlDomParser($html);

        $element = $document->find('div', 0);
        self::assertEquals($attr, $element->getAllAttributes());

        $element = $document->find('div', 1);
        self::assertNull($element->getAllAttributes());
    }

    public function testGetAttribute()
    {
        $html = '<div class="post" id="p1">foo</div>';

        $document = new HtmlDomParser($html);
        $element = $document->find('div', 0);

        self::assertEquals('post', $element->getAttribute('class'));
        self::assertEquals('post', $element->class);
        self::assertEquals('p1', $element->getAttribute('id'));
        self::assertEquals('p1', $element->id);
    }

    public function testSetAttribute()
    {
        $html = '<div class="post" id="p1">foo</div>';

        $document = new HtmlDomParser($html);
        $element = $document->find('div', 0);

        $element->setAttribute('id', 'bar');
        $element->data = 'value';
        unset($element->class);

        self::assertEquals('bar', $element->getAttribute('id'));
        self::assertEquals('value', $element->getAttribute('data'));
        self::assertEmpty($element->getAttribute('class'));
    }

    public function testHasAttribute()
    {
        $html = '<div class="post" id="p1">foo</div>';

        $document = new HtmlDomParser($html);
        $element = $document->find('div', 0);

        self::assertTrue($element->hasAttribute('class'));
        self::assertTrue(isset($element->id));
    }
}
