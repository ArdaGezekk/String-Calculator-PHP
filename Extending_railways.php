<?php

/**
* @author     Arda GEZEK
* @website    www.gezek.net
* @datetime   15 June 2020
* @purpose    "Extending railways"
*/

namespace extending_Railways;
use \Exception;
error_reporting(0);
const      Number                = 1;         // numbers
const      Ident                 = 2;         // constant
const      Functions             = 3;         // functions
const      Opening_Parenthesis   = 4;         // (
const      Closing_Parenthesis   = 5;         // )
const      Comma                 = 10;        // ,
const      Operators             = 20;        // +-*/^
const      Plus                  = 21;        // +
const      Minus                 = 22;        // -
const      Multiplication        = 23;        // *
const      Division              = 24;        // /
const      Power                 = 25;        // ^
const      Positive_Plus         = 26;        // +364
const      Negative_Minus        = 27;        // -364
const      Equal                 = 28;        // =

class token
{
  public $type, $value, $argc = 0;

  public function __construct($type, $value)
  {
    $this->type  = $type;
    $this->value = $value;
  }
}

class Constants
{
  protected $fnt = array(), $cst = array( 'pi' => M_PI, 'e' => M_E);

  public function fn($name, array $args)
  {
    if(in_array($name, array('sin', 'cos', 'tan')))
    {
      foreach ($args as $k => $arg) {
        $args[$k] = deg2rad($arg);
      }
    }
     return (float) call_user_func_array($name, $args);
  }

  public function cs($name)
  {
    $this->cst = array_merge($this->cst, $_SESSION);
    return $this->cst[$name];
  }

  public function def($name, $value = null)
  {
    if ($value === null) $value = $name;

    if (is_callable($value))
      $this->fnt[$name] = $value;

    elseif (is_numeric($value))
      $this->cst[$name] = (float) $value;

  }
}

class Calculate
{
  const State1 = 1;
  const State2 = 2;

  protected $scanning, $state = self::State1;
  protected $queue, $stack;

  public function __construct(scanning $scanning)
  {
    $this->scanning = $scanning;

    $this->queue = array();
    $this->stack = array();

    while (($t = $this->scanning->next()) !== false)
      $this->handle($t);

    while ($t = array_pop($this->stack)) {
      $this->queue[] = $t;
    }
  }

  public function reduce(Constants $ctx)
  {
    $this->stack = array();
    $length = 0;

    while ($t = array_shift($this->queue)) {
      switch ($t->type) {
        case Number:
        case Ident:
          if ($t->type === Ident)
            $t = new token(Number, $ctx->cs($t->value));


          $this->stack[] = $t;
          ++$length;
          break;

        case Plus:
        case Minus:
        case Positive_Plus:
        case Negative_Minus:
        case Multiplication:
        case Division:
        case Power:
          $na = $this->argc($t);

          $secondterm = array_pop($this->stack);
          $firstterm = null;

          if ($na > 1)
            $firstterm = array_pop($this->stack);

          $length -= $na - 1;

          $this->stack[] = new token(Number, $this->op($t->type, $firstterm, $secondterm));
          break;

        case Functions:
          $argc = $t->argc;
          $argv = array();

          $length -= $argc - 1;

          for (; $argc > 0; --$argc)
            array_unshift($argv, array_pop($this->stack)->value);

          $this->stack[] = new token(Number, $ctx->fn($t->value, $argv));
          break;

      }
    }
    if (count($this->stack) === 1)
      return array_pop($this->stack)->value;
  }

  protected function op($op, $firstterm, $secondterm)
  {
    if ($firstterm !== null) {
      $firstterm = $firstterm->value;
      $secondterm = $secondterm->value;

      switch ($op) {
        case Plus:
          return $firstterm + $secondterm;

        case Minus:
          return $firstterm - $secondterm;

        case Multiplication:
          return $firstterm * $secondterm;

        case Division:
          return $firstterm / $secondterm;

        case Power:
          return (float) pow($firstterm, $secondterm);
      }

      return 0;
    }

    switch ($op) {
      case Negative_Minus:
        return -$secondterm->value;

      case Positive_Plus:
        return +$secondterm->value;
    }
  }

  protected function argc(token $t)
  {
    switch ($t->type) {
      case Plus:
      case Minus:
      case Multiplication:
      case Division:
      case Power:
        return 2;
    }

    return 1;
  }

  public function clear($str = false)
  {
    if ($str === false) {
      print_r($this->queue);
      return;
    }

    $res = array();

    foreach ($this->queue as $t) {
      $val = $t->value;

      switch ($t->type) {
        case Negative_Minus:
        case Positive_Plus:
          $val = 'unary' . $val;
          break;
      }

      $res[] = $val;
    }

    print implode(' ', $res);
  }

  protected function fargs($fn)
  {
    $this->handle($this->scanning->next()); // '('

    $argc = 0;
    $next = $this->scanning->look();

    if ($next && $next->type !== Closing_Parenthesis) {
      $argc = 1;

      while ($t = $this->scanning->next()) {
        $this->handle($t);

        if ($t->type === Closing_Parenthesis)
          break;

        if ($t->type === Comma)
          ++$argc;
      }
    }

    $fn->argc = $argc;
  }

  protected function handle(token $t)
  {
    switch ($t->type) {
      case Number:
      case Ident:
        $this->queue[] = $t;
        $this->state = self::State2;
        break;

      case Functions:
        $this->stack[] = $t;
        $this->fargs($t);
        break;


      case Comma:

        $pe = false;

        while ($t = end($this->stack)) {
          if ($t->type === Opening_Parenthesis) {
            $pe = true;
            break;
          }
          $this->queue[] = array_pop($this->stack);
        }


      case Plus:
      case Minus:
      case Positive_Plus:
      case Negative_Minus:
      case Multiplication:
      case Division:
      case Power:
        while (!empty($this->stack)) {
          $s = end($this->stack);


          switch ($s->type) {
            default: break 2;

            case Plus:
            case Minus:
            case Positive_Plus:
            case Negative_Minus:
            case Multiplication:
            case Division:
            case Power:
              $p1 = $this->preced($t);
              $p2 = $this->preced($s);

              if (!(($this->assoc($t) === 1 && ($p1 <= $p2)) || ($p1 < $p2)))
                break 2;

              $this->queue[] = array_pop($this->stack);
          }
        }

        $this->stack[] = $t;
        $this->state = self::State1;
        break;

      case Opening_Parenthesis:
        $this->stack[] = $t;
        $this->state = self::State1;
        break;

      case Closing_Parenthesis:
        $pe = false;


        while ($t = array_pop($this->stack)) {
          if ($t->type === Opening_Parenthesis) {
            $pe = true;
            break;
          }

          $this->queue[] = $t;
        }

          if (($t = end($this->stack)) && $t->type === Functions)
          $this->queue[] = array_pop($this->stack);

        $this->state = self::State2;
        break;
        }
      }

  protected function assoc(token $t)
  {
    switch ($t->type) {
      case Multiplication:
      case Division:

      case Plus:
      case Minus:
        return 1; //Left to Right
      case Positive_Plus:
      case Negative_Minus:

      case Power:
        return 2; //Tight to Left
    }

    return 0;
  }

  protected function preced(token $t)
  {
    switch ($t->type) {
      case Positive_Plus:
      case Negative_Minus:
        return 4;

      case Power:
        return 3;

      case Multiplication:
      case Division:
        return 2;

      case Plus:
      case Minus:
        return 1;
    }

    return 0;
  }

  public static function it($term, Constants $ctx = null)
  {
      $obj = new self(new scanning($term));
      return $obj
              ->reduce($ctx ?: new Constants);
  }
}

class scanning
{
  //                      operators           numbers           letter    Space between operation
  const PATTERN = '/^([!,\+\-\*\/\^\(\)]|\d*\.\d+|\d+\.\d*|\d+|[a-z_A-Z]+[a-z_A-Z0-9]*|[ \t]+)/';


  protected $tokens = array( 0 );

  protected $check = array(
    '+' => Plus,
    '-' => Minus,
    '/' => Division,
    '^' => Power,
    '*' => Multiplication,
    '(' => Opening_Parenthesis,
    ')' => Closing_Parenthesis,
    ',' => Comma
  );

  public function __construct($input)
  {
    session_start();
    $prev = new token(Operators, 'noop');
    $firstInput = $input;
    while (trim($input) !== '') {

      if (!preg_match(self::PATTERN, $input, $compare)) {
        $value = explode('=',$firstInput)[1];
        $name  = explode('=',$firstInput)[0];

        $_SESSION[trim($name)] = $value;
        return;
      }

      if (empty($compare[1]) && $compare[1] !== '0') {
      }

      $input = substr($input, strlen($compare[1]));

      if (($value = trim($compare[1])) === '') {
        continue;
      }

      if (is_numeric($value)) {
        if ($prev->type === Closing_Parenthesis)
          $this->tokens[] = new token(Multiplication, '*');

        $this->tokens[] = $prev = new token(Number, (float) $value);
        continue;
      }

      switch ($type = isset($this->check[$value]) ? $this->check[$value] : Ident) {
        case Plus:
          if ($prev->type & Operators || $prev->type == Opening_Parenthesis) $type = Positive_Plus;
          break;

        case Minus:
          if ($prev->type & Operators || $prev->type == Opening_Parenthesis) $type = Negative_Minus;
          break;

        case Opening_Parenthesis:
          switch ($prev->type) {
            case Ident:
              $prev->type = Functions;
              break;

            case Number:
            case Closing_Parenthesis:
              $this->tokens[] = new token(Multiplication, '*');
              break;
          }
          break;
      }

      $this->tokens[] = $prev = new token($type, $value);
    }
  }

  public function currents() { return current($this->tokens); }
  public function next() { return next($this->tokens); }
  public function previous() { return prev($this->tokens); }
  public function clear() { print_r($this->tokens); }

  public function look()
  {
    $v = next($this->tokens);
    prev($this->tokens);

    return $v;
  }
}
