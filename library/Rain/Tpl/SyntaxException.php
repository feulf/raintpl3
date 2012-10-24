<?php
namespace Rain\Tpl;

/**
 * Exception thrown when syntax error occurs.
 */
class SyntaxException extends Exception {

    /**
     * Line in template file where error has occured.
     *
     * @var int | null
     */
    protected $templateLine = null;

    /**
     * Tag which caused an error.
     *
     * @var string | null
     */
    protected $tag = null;

    /**
     * Returns line in template file where error has occured
     * or null if line is not defined.
     *
     * @return int | null
     */
    public function getTemplateLine() {
        return $this->templateLine;
    }

    /**
     * Sets  line in template file where error has occured.
     *
     * @param int $templateLine
     * @return RainTpl_SyntaxException
     */
    public function setTemplateLine($templateLine) {
        $this->templateLine = (int) $templateLine;
        return $this;
    }

    /**
     * Returns tag which caused an error.
     *
     * @return string
     */
    public function getTag() {
        return $this->tag;
    }

    /**
     * Sets tag which caused an error.
     *
     * @param string $tag
     * @return RainTpl_SyntaxException
     */
    public function setTag($tag) {
        $this->tag = (string) $tag;
        return $this;
    }

}
