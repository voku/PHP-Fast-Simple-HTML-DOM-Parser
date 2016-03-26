<?php

namespace FastSimpleHTMLDom;


use BadMethodCallException;
use DOMElement;
use DOMNode;
use RuntimeException;

/**
 * Class SimpleHtmlDom
 * 
*@package FastSimpleHTMLDom
 * @property string      outertext Get dom node's outer html
 * @property string      innertext Get dom node's inner html
 * @property string plaintext (read-only) Get dom node's plain text
 * @property string tag       (read-only) Get dom node name
 * @property string attr      (read-only) Get dom node attributes
 *
 * @method SimpleHtmlDomNode|SimpleHtmlDom|null children() children($idx = -1) Returns children of node
 * @method SimpleHtmlDom|null first_child() Returns the first child of node
 * @method SimpleHtmlDom|null last_child() Returns the last child of node
 * @method SimpleHtmlDom|null next_sibling() Returns the next sibling of node
 * @method SimpleHtmlDom|null prev_sibling() Returns the previous sibling of node
 * @method SimpleHtmlDom|null parent() Returns the parent of node
 * @method string outertext() Get dom node's outer html
 * @method string innertext() Get dom node's inner html
 */
class SimpleHtmlDom implements \IteratorAggregate
{
    /**
     * @var DOMElement
     */
    protected $node;

    /**
     * @var array
     */
    protected $functionAliases = array(
        'children'     => 'childNodes',
        'first_child'  => 'firstChild',
        'last_child'   => 'lastChild',
        'next_sibling' => 'nextSibling',
        'prev_sibling' => 'previousSibling',
        'parent'       => 'parentNode',
        'outertext'    => 'html',
        'innertext'    => 'innerHtml',
    );

  /**
   * SimpleHtmlDom constructor.
   *
   * @param DOMNode $node
   */
    public function __construct(DOMNode $node)
    {
        $this->node = $node;
    }

    /**
     * @return DOMNode
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Replace this node
     *
     * @param $string
     *
     * @return $this
     */
    protected function replaceNode($string)
    {
        if (empty($string)) {
            $this->node->parentNode->removeChild($this->node);

            return null;
        }

        $newDocument = new HtmlDomParser($string);

        $newNode = $this->node->ownerDocument->importNode($newDocument->getDocument()->documentElement, true);

        $this->node->parentNode->replaceChild($newNode, $this->node);
        $this->node = $newNode;

        return $this;
    }

    /**
     * Replace child node
     *
     * @param $string
     *
     * @return $this
     */
    protected function replaceChild($string)
    {
        if (!empty($string)) {
            $newDocument = new HtmlDomParser($string);
        }

        foreach ($this->node->childNodes as $node) {
            $this->node->removeChild($node);
        }

        if (!empty($newDocument)) {
            $newNode = $this->node->ownerDocument->importNode($newDocument->getDocument()->documentElement, true);
            $this->node->appendChild($newNode);
        }

        return $this;
    }

    /**
     * @return HtmlDomParser
     */
    public function getDom()
    {
        return new HtmlDomParser($this);
    }

    /**
     * Find list of nodes with a CSS selector
     *
     * @param string $selector
     * @param int    $idx
     *
     * @return SimpleHtmlDomNode|SimpleHtmlDomNode[]|SimpleHtmlDomNodeBlank
     */
    public function find($selector, $idx = null)
    {
        return $this->getDom()->find($selector, $idx);
    }

    /**
     * Return SimpleHtmlDom by id
     *
     * @param $id
     *
     * @return SimpleHtmlDom|null
     */
    public function getElementById($id)
    {
        return $this->find("#$id", 0);
    }

    /**
     * Returns Elements by id
     *
     * @param      $id
     * @param null $idx
     *
     * @return SimpleHtmlDom|SimpleHtmlDomNode|null
     */
    public function getElementsById($id, $idx = null)
    {
        return $this->find("#$id", $idx);
    }

    /**
     * Return SimpleHtmlDom by tag name
     *
     * @param $name
     *
     * @return SimpleHtmlDom|null
     */
    public function getElementByTagName($name)
    {
        return $this->find($name, 0);
    }

    /**
     * Returns Elements by tag name
     *
     * @param      $name
     * @param null $idx
     *
     * @return SimpleHtmlDom|SimpleHtmlDomNode|null
     */
    public function getElementsByTagName($name, $idx = null)
    {
        return $this->find($name, $idx);
    }

    /**
     * Returns children of node
     *
     * @param int $idx
     *
     * @return SimpleHtmlDomNode|SimpleHtmlDom|null
     */
    public function childNodes($idx = -1)
    {
        $nodeList = $this->getIterator();

        if ($idx === -1) {
            return $nodeList;
        }

        if (isset($nodeList[$idx])) {
            return $nodeList[$idx];
        }

        return null;
    }

    /**
     * Returns the first child of node
     *
     * @return SimpleHtmlDom|null
     */
    public function firstChild()
    {
        $node = $this->node->firstChild;

        if ($node === null) {
            return null;
        }

        return new SimpleHtmlDom($node);
    }

    /**
     * Returns the last child of node
     *
     * @return SimpleHtmlDom|null
     */
    public function lastChild()
    {
        $node = $this->node->lastChild;

        if ($node === null) {
            return null;
        }

        return new SimpleHtmlDom($node);
    }

    /**
     * Returns the next sibling of node
     *
     * @return SimpleHtmlDom|null
     */
    public function nextSibling()
    {
        $node = $this->node->nextSibling;

        if ($node === null) {
            return null;
        }

        return new SimpleHtmlDom($node);
    }

    /**
     * Returns the previous sibling of node
     *
     * @return SimpleHtmlDom|null
     */
    public function previousSibling()
    {
        $node = $this->node->previousSibling;

        if ($node === null) {
            return null;
        }

        return new SimpleHtmlDom($node);
    }

    /**
     * Returns the parent of node
     *
     * @return SimpleHtmlDom
     */
    public function parentNode()
    {
        return new SimpleHtmlDom($this->node->parentNode);
    }

    /**
     * Get dom node's outer html
     *
     * @return string
     */
    public function html()
    {
        return $this->getDom()->html();
    }

    /**
     * Get dom node's inner html
     *
     * @return string
     */
    public function innerHtml()
    {
        return $this->getDom()->innerHtml();
    }

    /**
     * Get dom node's plain text
     *
     * @return string
     */
    public function text()
    {
        return $this->node->textContent;
    }

    /**
     * Returns array of attributes
     *
     * @return array|null
     */
    public function getAllAttributes()
    {
        if ($this->node->hasAttributes()) {
            $attributes = array();
            foreach ($this->node->attributes as $attr) {
                $attributes[$attr->name] = $attr->value;
            }

            return $attributes;
        }

        return null;
    }

    /**
     * Return attribute value
     *
     * @param string $name
     *
     * @return string|null
     */
    public function getAttribute($name)
    {
        return $this->node->getAttribute($name);
    }

    /**
     * Set attribute value
     *
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function setAttribute($name, $value)
    {
        if (empty($value)) {
            $this->node->removeAttribute($name);
        } else {
            $this->node->setAttribute($name, $value);
        }

        return $this;
    }

    /**
     * Determine if an attribute exists on the element.
     *
     * @param $name
     *
     * @return bool
     */
    public function hasAttribute($name)
    {
        return $this->node->hasAttribute($name);
    }

    /**
     * @param $name
     *
     * @return array|null|string
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
            case 'tag'      :
                return $this->node->nodeName;
            case 'attr'     :
                return $this->getAllAttributes();
            default         :
                return $this->getAttribute($name);
        }
    }

  /**
   * @param $name
   * @param $value
   *
   * @return SimpleHtmlDom
   */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'outertext':
                return $this->replaceNode($value);
            case 'innertext':
                return $this->replaceChild($value);
            default         :
                return $this->setAttribute($name, $value);
        }
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        switch ($name) {
            case 'outertext':
            case 'innertext':
            case 'plaintext':
            case 'tag'      :
                return true;
            default         :
                return $this->hasAttribute($name);
        }
    }

  /**
   * @param $name
   *
   * @return SimpleHtmlDom
   */
    public function __unset($name)
    {
        return $this->setAttribute($name, null);
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
     * @return null|string|SimpleHtmlDom
     *
     */
    public function __call($name, $arguments)
    {
        if (isset($this->functionAliases[$name])) {
            return call_user_func_array(array($this, $this->functionAliases[$name]), $arguments);
        }
        throw new BadMethodCallException('Method does not exist');
    }

    /**
     * Retrieve an external iterator
     * 
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return SimpleHtmlDomNode An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        $elements = new SimpleHtmlDomNode();
        if ($this->node->hasChildNodes()) {
            foreach ($this->node->childNodes as $node) {
                $elements[] = new SimpleHtmlDom($node);
            }
        }

        return $elements;
    }
}
