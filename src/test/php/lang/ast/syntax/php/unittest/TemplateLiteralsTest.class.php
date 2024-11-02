<?php namespace lang\ast\syntax\php\unittest;

use lang\ast\Errors;
use lang\ast\unittest\emit\EmittingTest;
use test\{Assert, Before, Expect, Test};

class TemplateLiteralsTest extends EmittingTest {
  private $format;

  /** Evaluates a strign template */
  private function evaluate(string $template, array $arguments= []) {
    return $this->type('class %T { public function run($f, $arguments) { return '.$template.'; } }')
      ->newInstance()
      ->run($this->format, $arguments)
    ;
  }

  #[Before]
  public function format() {
    $this->format= function($strings, ... $arguments) {
      $r= '';
      foreach ($strings as $i => $string) {
        $r.= $string.htmlspecialchars($arguments[$i] ?? '');
      }
      return $r;
    };
  }

  #[Test]
  public function without_placeholders() {
    Assert::equals('Test', $this->evaluate('$f`Test`'));
  }

  #[Test]
  public function escaped_backtick() {
    Assert::equals('Command: `ls -al`', $this->evaluate('$f`Command: \`ls -al\``'));
  }

  #[Test]
  public function support_escape_sequences() {
    Assert::equals("A\n€", $this->evaluate('$f`A\n\u{20ac}`'));
  }

  #[Test]
  public function does_not_interpolate_variables() {
    Assert::equals('Used $literally', $this->evaluate('$f`Used $literally`'));
  }

  #[Test]
  public function placeholder_at_beginning() {
    Assert::equals('test', $this->evaluate('$f`${"test"}`'));
  }

  #[Test]
  public function evaluates_arguments() {
    Assert::equals('2 + 3 = 5', $this->evaluate('$f`2 + 3 = ${2 + 3}`'));
  }

  #[Test]
  public function dollar_sign() {
    Assert::equals('Price is $1.99', $this->evaluate('$f`Price is $${1.99}`'));
  }

  #[Test]
  public function braces() {
    Assert::equals('Supported on '.PHP_OS, $this->evaluate('$f`Supported on ${match (true) { default => PHP_OS }}`'));
  }

  #[Test]
  public function evaluates_global_constant() {
    Assert::equals('PHP_OS = '.PHP_OS, $this->evaluate('$f`PHP_OS = ${PHP_OS}`'));
  }

  #[Test]
  public function argument_passed() {
    Assert::equals(
      'This is a <a href="https://example.com/?a&amp;b">link</a>.',
      $this->evaluate('$f`This is a <a href="${$arguments[0]}">link</a>.`', ['https://example.com/?a&b'])
    );
  }

  #[Test]
  public function quoted_string() {
    Assert::equals(
      'He said "Test"',
      $this->evaluate('$f`He said "Test"`')
    );
  }

  #[Test]
  public function ternary_expression() {
    Assert::equals(
      'You are 17, you can not yet vote.',
      $this->evaluate('$f`You are ${$arguments[0]}, you can ${$arguments[0] < 18 ? "not yet vote" : "vote"}.`', [17])
    );
  }

  #[Test]
  public function resolve_via_method() {
    $t= $this->type('class %T {
      private function plain($strings, ... $arguments) {
        return implode("", $strings);
      }

      public function run() {
        return $this->plain`Test`;
      }
    }');

    Assert::equals('Test', $t->newInstance()->run());
  }

  #[Test]
  public function can_return_other_types_than_string() {
    $t= $this->type('class %T {
      private $base= "//example.com";

      private function resource($strings, ... $arguments) {
        $path= "";
        foreach ($strings as $i => $string) {
          $path.= $string.rawurlencode($arguments[$i] ?? "");
        }
        return ["base" => $this->base, "path" => $path];
      }

      public function run($userId) {
        return $this->resource`/users/${$userId}`;
      }
    }');

    Assert::equals(['base' => '//example.com', 'path' => '/users/%40me'], $t->newInstance()->run('@me'));
  }

  #[Test, Expect(class: Errors::class, message: '/Unexpected string "Test" \[line 1 of .+\]/')]
  public function cannot_suffix_other_literals() {
    $this->evaluate('$f"Test"');
  }
}