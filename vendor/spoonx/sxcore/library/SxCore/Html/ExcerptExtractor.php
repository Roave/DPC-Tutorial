<?php

namespace SxCore\Html;

use \DOMDocument;
use \DOMText;
use SxCore\Html\Exception;

class ExcerptExtractor
{

    /**
     * @var \DOMDocument
     */
    protected $DOMDocument;

    /**
     * @param $markup
     */
    public function __construct($markup = null)
    {
        if (null !== $markup) {
            $this->setMarkup($markup);
        }
    }

    /**
     * @param $markup
     *
     * @throws \SxCore\Html\Exception\InvalidArgumentException
     */
    public function setMarkup($markup)
    {
        if (!is_string($markup)) {
            throw new Exception\InvalidArgumentException(
                'Expected string. Got "' . gettype($markup) . '".'
            );
        }

        $this->DOMDocument = new DOMDocument;

        $this->DOMDocument->loadHTML($markup);
    }

    /**
     * Get the excerpt for constructed markup.
     *  Note: Text-only values will be wrapped in a paragraph tag.
     *
     * @param $length
     *
     * @return mixed
     */
    public function getExcerpt($length)
    {
        $elements = $this->DOMDocument->getElementsByTagName('body')->item(0);
        $trimmed  = $this->trimMarkup($elements->childNodes, $length);

        return $trimmed['markup'];
    }

    /**
     * @param $element
     * @param $length
     *
     * @return array
     */
    protected function trimDOMTextElement($element, $length)
    {
        $textMetLimit = false;
        $textLength   = strlen($element->nodeValue);

        if ($textLength >= ($length - 30)) {
            $textMetLimit       = true;
            $element->nodeValue = substr($element->nodeValue, 0, ($length - $textLength)) . '...';
        }

        return array(
            'text_met_limit' => $textMetLimit,
            'element'        => $element,
            'text_length'    => $textLength,
        );
    }

    /**
     * @param $elements
     * @param $length
     *
     * @throws Exception\RuntimeException
     * @return array
     */
    protected function trimMarkup($elements, $length)
    {
        $markup     = '';
        $textLength = 0;

        foreach ($elements as $element) {

            $maxLength         = $length - $textLength;
            $renderAlternative = false;

            if ($element->firstChild instanceof DOMText) {
                $renderAlternative = $element;
                $element           = $element->firstChild;
            }

            if ($element instanceof DOMText) {
                $trimmedElement = $this->trimDOMTextElement($element, $maxLength);
                $textLength     = $textLength + $trimmedElement['text_length'];

                if (false !== $renderAlternative) {
                    $renderAlternative->firstChild->nodeValue = $this->DOMDocument->saveHTML($trimmedElement['element']);
                    $trimmedElement['element']                = $renderAlternative;
                }

                $markup = $markup . $this->DOMDocument->saveHTML($trimmedElement['element']);

                if ($trimmedElement['text_met_limit']) {
                    return array(
                        'finished' => true,
                        'markup'   => $markup,
                        'element'  => $elements,
                        'length'   => $textLength,
                    );
                }

                continue;
            }

            throw new Exception\RuntimeException(
                'Invalid markup detected. Only supports single-level dept.'
            );
        }

        return array(
            'finished' => false,
            'markup'   => $markup,
            'element'  => $elements,
            'length'   => $textLength,
        );
    }
}
