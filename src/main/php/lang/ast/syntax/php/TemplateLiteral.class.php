<?php namespace lang\ast\syntax\php;

use lang\ast\Node;

class TemplateLiteral extends Node {
  public $kind= 'template';
  public $resolver, $strings, $arguments;

  public function __construct($resolver, $strings, $arguments, $line= -1) {
    $this->resolver= $resolver;
    $this->strings= $strings;
    $this->arguments= $arguments;
    $this->line= $line;
  }

  /** @return iterable */
  public function children() { return []; }
}