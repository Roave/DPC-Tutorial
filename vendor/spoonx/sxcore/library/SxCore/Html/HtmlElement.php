<?php

/**
 * @category SxCore
 * @package  Html
 */
namespace SxCore\Html;

use SxCore\Html\Exception;

class HtmlElement
{

    /**
     * The tag to render
     *
     * @var string
     */
    protected $tag;

    /**
     * The children of the tag
     *
     * @var array
     */
    protected $children = array();

    /**
     * The attributes of the tag
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * The content of the tag
     *
     * @var string
     */
    protected $content = '';

    /**
     * Append, or prepend set content next to rendered children
     *
     * @var string  prepend|append
     */
    protected $contentConcat = 'prepend';

    /**
     * @var boolean
     */
    protected $isVoid = false;

    /**
     * @var boolean
     */
    protected $isXhtml = false;

    /**
     * @var array
     *
     * @see http://www.w3.org/TR/html-markup/syntax.html#syntax-elements
     */
    protected $voidElements = array(
        'area',
        'base',
        'br',
        'col',
        'command',
        'embed',
        'hr',
        'img',
        'input',
        'keygen',
        'link',
        'meta',
        'param',
        'source',
        'track',
        'wbr',
    );

    /**
     * Construct tag
     *
     * @param string $tag The tag name (div, span, p, table etc).
     */
    public function __construct($tag = 'div')
    {
        if ($this->isVoidElement($tag)) {
            $this->setVoid();
        }

        $this->tag = $tag;
    }

    /**
     * @param string $tag
     *
     * @return boolean
     */
    public function isVoidElement($tag = null)
    {
        if (null === $tag) {
            $tag = $this->getTag();
        }

        return in_array($tag, $this->voidElements);
    }

    /**
     * @param boolean $isXhtml
     *
     * @return \SxCore\Html\HtmlElement
     */
    public function setIsXhtml($isXhtml = true)
    {
        $this->isXhtml = (bool) $isXhtml;

        return $this;
    }

    /**
     * Set the element to be void.
     *
     * @param boolean $void
     *
     * @return \SxCore\Html\HtmlElement
     * @see    http://www.w3.org/TR/html-markup/syntax.html#syntax-elements
     */
    public function setVoid($void = true)
    {
        $this->isVoid = (bool) $void;

        return $this;
    }

    /**
     * Set position of content to append
     *
     * @return  \SxCore\Html\HtmlElement
     */
    public function setAppendContent()
    {
        $this->contentConcat = 'append';

        return $this;
    }

    /**
     * Set position of content to prepend
     *
     * @return  \SxCore\Html\HtmlElement
     */
    public function setPrependContent()
    {
        $this->contentConcat = 'prepend';

        return $this;
    }

    /**
     * Set tag attributes
     *
     * @param array $attributes
     *
     * @return  \SxCore\Html\HtmlElement
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Add tag attributes
     *
     * @param array $attributes
     *
     * @return  \SxCore\Html\HtmlElement
     */
    public function addAttributes(array $attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    /**
     *
     * @param string $key
     * @param string $value
     *
     * @return  \SxCore\Html\HtmlElement
     *
     * @throws  \SxCore\Html\Exception\InvalidArgumentException
     */
    public function addAttribute($key, $value = null)
    {
        if (!is_string($key) || ((!is_string($value)) && !is_numeric($value) && null !== $value)) {
            throw new Exception\InvalidArgumentException(
                'Invalid key or value type supplied. Expected string.'
            );
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return HtmlElement
     */
    public function removeAttribute($key)
    {
        if (!empty($this->attributes[$key])) {
            unset($this->attributes[$key]);
        }

        return $this;
    }

    /**
     * Get tag attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Add class to tag
     *
     * @param string $class
     *
     * @return  \SxCore\Html\HtmlElement
     */
    public function addClass($class)
    {
        if (!empty($this->attributes['class'])) {
            $class = $this->attributes['class'] . " $class";
        }

        return $this->addAttribute('class', $class);
    }

    /**
     * Set the content. (Overwrites old content)
     *
     * @param string $content
     *
     * @throws Exception\RuntimeException
     * @return \SxCore\Html\HtmlElement
     */
    public function setContent($content)
    {
        if ($this->isVoid) {
            throw new Exception\RuntimeException(
                'Void elements can\'t contain content.'
            );
        }

        $this->content = $content;

        return $this;
    }

    /**
     * Append content before other content
     *
     * @param string $content
     *
     * @throws Exception\RuntimeException
     * @return  \SxCore\Html\HtmlElement
     */
    public function appendContent($content)
    {
        if ($this->isVoid) {
            throw new Exception\RuntimeException(
                'Void elements can\'t contain content.'
            );
        }

        $this->content .= $content;

        return $this;
    }

    /**
     * Prepend content before other content
     *
     * @param string $content
     *
     * @throws Exception\RuntimeException
     * @return  \SxCore\Html\HtmlElement
     */
    public function prependContent($content)
    {
        if ($this->isVoid) {
            throw new Exception\RuntimeException(
                'Void elements can\'t contain content.'
            );
        }

        $this->content = $content . $this->content;

        return $this;
    }

    /**
     * Remove content
     *
     * @return \SxCore\Html\HtmlElement
     */
    public function removeContent()
    {
        $this->content = '';

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Spawn child
     *
     * @param string $tag
     *
     * @throws Exception\RuntimeException
     * @return \SxCore\Html\HtmlElement
     */
    public function spawnChild($tag = 'div')
    {
        if ($this->isVoid) {
            throw new Exception\RuntimeException(
                'Void elements can\'t have child elements.'
            );
        }

        return $this->children[] = new self($tag);
    }

    /**
     * Add child to tag
     *
     * @param \SxCore\Html\HtmlElement $child
     *
     * @throws Exception\RuntimeException
     * @return \SxCore\Html\HtmlElement
     */
    public function addChild(self $child)
    {
        if ($this->isVoid) {
            throw new Exception\RuntimeException(
                'Void elements can\'t have child elements.'
            );
        }

        $this->children[] = $child;

        return $this;
    }

    /**
     * Add children to tag
     *
     * @param array $children
     *
     * @throws Exception\RuntimeException
     * @return  \SxCore\Html\HtmlElement
     */
    public function addChildren(array $children)
    {
        if ($this->isVoid) {
            throw new Exception\RuntimeException(
                'Void elements can\'t have child elements.'
            );
        }

        foreach ($children as $child) {
            $this->addChild($child);
        }

        return $this;
    }

    /**
     * Remove children
     *
     * @return  \SxCore\Html\HtmlElement
     */
    public function removeChildren()
    {
        $this->children = array();

        return $this;
    }

    /**
     * Set children
     *
     * @param array $children
     *
     * @return  \SxCore\Html\HtmlElement
     */
    public function setChildren(array $children)
    {
        $this->removeChildren();

        return $this->addChildren($children);
    }

    /**
     * Check for children
     *
     * @return bool
     */
    public function hasChildren()
    {
        return !empty($this->children);
    }

    /**
     * Render content
     *
     * @return string
     */
    public function render()
    {
        if ($this->isVoid) {
            return $this->renderTag();
        }

        $content = '';

        if ($this->hasChildren()) {
            $content = $this->renderChildren();
        }

        if ('append' === $this->contentConcat) {
            $content .= $this->getContent();
        } else {
            $content = $this->getContent() . $content;
        }

        return $this->renderTag($content);
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Render tag
     *
     * @param string $content
     *
     * @return string
     */
    protected function renderTag($content = null)
    {
        $attributes = $this->renderAttributes();

        if ($this->isVoid) {
            return sprintf(
                '<%1$s%2$s%3$s>',
                $this->tag,
                $attributes,
                $this->isXhtml ? ' /' : ''
            );
        }

        return sprintf(
            '<%1$s%2$s>%3$s</%1$s>',
            $this->tag,
            $attributes,
            $content
        );
    }

    /**
     * Render tag attributes
     *
     * @return string
     */
    protected function renderAttributes()
    {
        $attributes = '';

        foreach ($this->attributes as $key => $value) {
            $attributes .= " $key" . (null !== $value ? "=\"$value\"" : '');
        }

        return $attributes;
    }

    /**
     * @return array|HtmlElement[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param null|HtmlElement[] $children
     *
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public function renderChildren($children = null)
    {
        if (null === $children) {
            $children = $this->getChildren();
        }

        if (!is_array($children)) {
            throw new Exception\InvalidArgumentException(
                'Invalid children type supplied. Expected array or null.'
            );
        }

        $content = '';

        /* @var $child HtmlElement */
        foreach ($children as $child) {
            $content .= $child->render();
        }

        return $content;
    }

    /**
     * Output tag
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
