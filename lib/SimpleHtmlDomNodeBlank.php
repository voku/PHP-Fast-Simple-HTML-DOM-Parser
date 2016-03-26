<?php

namespace FastSimpleHTMLDom;


/**
 * Class SimpleHtmlDomNodeBlank
 * @package FastSimpleHTMLDom
 * @property-read string outertext Get dom node's outer html
 * @property-read string plaintext Get dom node's plain text
 */
class SimpleHtmlDomNodeBlank extends \ArrayObject
{
  /**
   * Get plain text
   *
   * @return string
   */
  public function text()
  {
    return '';
  }

  /**
   * Get html of Elements
   *
   * @return string
   */
  public function innerHtml()
  {
    return '';
  }

  /**
   * @param $name
   *
   * @return string
   */
  public function __get($name)
  {
    return '';
  }

  /**
   * @return mixed
   */
  public function __toString()
  {
    return $this->innerHtml();
  }

  /**
   * @param string $selector
   * @param int    $idx
   *
   * @return SimpleHtmlDom|SimpleHtmlDomNode|null
   */
  public function __invoke($selector, $idx = null)
  {
  }
}
