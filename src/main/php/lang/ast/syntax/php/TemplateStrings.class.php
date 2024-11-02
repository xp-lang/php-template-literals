<?php namespace lang\ast\syntax\php;

use lang\ast\nodes\{ArrayLiteral, InvokeExpression, Literal};
use lang\ast\syntax\Extension;
use lang\ast\{Error, Tokens};

class TemplateStrings implements Extension {

  public function setup($language, $emitter) {
    $language->suffix('(literal)', 100, function($parse, $token, $left) {
      static $escape= ['"' => '\\"', '\\`' => '`', '$' => '\\$'];

      if ('string' !== $token->kind || '`' !== $token->value[0]) {
        throw new Error("Unexpected {$token->kind} {$token->value}", $parse->file, $token->line);
      }

      $strings= [];
      $arguments= [];
      for ($o= 1, $l= strlen($token->value) - 1; $o < $l; ) {
        $p= strpos($token->value, '${', $o);

        // Everything before the placeholder is a string literal
        $strings[]= new Literal('"'.($p === $o ? '' : strtr(substr($token->value, $o, ($p ?: $l) - $o), $escape)).'"');
        if (false === $p) break;

        // Everything following is the placeholder, which may contain `{}`
        for ($o= $p+= 2, $b= 1; $b && $o < $l; $o++) {
          $o+= strcspn($token->value, '{}', $o);
          if ('{' === $token->value[$o]) {
            $b++;
          } else if ('}' === $token->value[$o]) {
            $b--;
          }
        }

        $expr= $this->parse(new Tokens(strtr(substr($token->value, $p, $o - $p - 1), ['\\`' => '`'])), $parse->scope);
        $expr->forward();
        $arguments[]= $this->expression($expr, 0);
      }

      return new TemplateLiteral($left, $strings, $arguments, $token->line);
    });

    $emitter->transform('template', function($codegen, $node) {
      $strings= new ArrayLiteral([]);
      foreach ($node->strings as $string) {
        $strings->values[]= [null, $string];
      }
      return new InvokeExpression($node->resolver, [$strings, ...$node->arguments]);
    });
  }
}