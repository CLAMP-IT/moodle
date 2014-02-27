<?php

/**
 * Monadic interface allows filtering, transforms, or safe
 * mutation for object wrappers
 */
interface Monadic {
  /**
   * Filter
   *
   * @param callable $callback (mixed => boolean)
   * @return mixed
   */
  public function filter($callback);

  /**
   * Transform
   *
   * @param callable $callback (mixed => mixed)
   * @return mixed
   */
  public function map($callback);

  /**
   * Mutate
   *
   * @param callable $callback (mixed => void)
   * @return mixed
   */
  public function each($callback);
}

/**
 * An option type is either carrying something or nothing
 */
abstract class Model_Option implements Monadic {
  /**
   * Is this option empty?
   *
   * @return boolean
   */
  public abstract function isEmpty();

  /**
   * Get the wrapped object
   *
   * @return mixed
   */
  public abstract function get();

  /**
   * Is this option defined?
   *
   * @return boolean
   */
  public function isDefined() {
    return !$this->isEmpty();
  }

  /**
   * @see parent
   * @return Model_Option
   */
  public function filter($callback) {
    if ($this->isDefined() && $callback($this->get())) {
      return new Model_Some($this->get());
    }

    return new Model_None();
  }

  /**
   * @see parent
   * @return Model_Option
   */
  public function map($callback) {
    if (!$this->isEmpty()) {
      return new Model_Some($callback($this->get()));
    }
    return $this;
  }

  /**
   * @see parent
   * @return Model_Option
   */
  public function each($callback) {
    if (!$this->isEmpty()) {
      $callback($this->get());
    }
    return $this;
  }

  /**
   * Make this option a Some with callback return
   *
   * @param callable $callback (void => mixed)
   * @return Model_Some
   */
  public function orElse($callback) {
    return $this->isDefined() ? $this : new Model_Some($callback());
  }

  /**
   * Get the underlying value or some default value
   *
   * @param mixed $default
   * @return mixed
   */
  public function getOrElse($default) {
    return $this->isDefined() ? $this->get() : $default;
  }
}

/**
 * An option type that is carrying something
 */
class Model_Some extends Model_Option {
  protected $identity;

  /**
   * Fill this something with .... something
   *
   * @param mixed $identity
   */
  public function __construct($identity) {
    $this->identity = $identity;
  }

  /**
   * @see parent
   * @return boolean
   */
  public function isEmpty() {
    return false;
  }

  /**
   * @see parent
   * @return mixed
   */
  public function get() {
    return $this->identity;
  }
}

/**
 * An option type that is carrying nothing
 */
class Model_None extends Model_Option {
  /**
   * @see parent
   * @return boolean
   */
  public function isEmpty() {
    return true;
  }

  /**
   * @see parent
   * @throws BadMethodCallException
   */
  public function get() {
    throw new BadMethodCallException("Can not call Model_None::get().");
  }
}

/**
 * A optional carrier that denotes one thing or another (maybe)
 */
abstract class Model_Either {
  protected $identity;

  /**
   * The wrapped object
   *
   * @param $identity
   */
  public function __construct($identity) {
    $this->identity = $identity;
  }

  /**
   * Is this maybe right leaning?
   *
   * @return boolean
   */
  public abstract function isRight();

  /**
   * Is this maybe left leaning?
   *
   * @return boolean
   */
  public function isLeft() {
    return !$this->isRight();
  }

  /**
   * Gets the wrapped object
   *
   * @return mixed
   */
  public function get() {
    return $this->identity;
  }

  /**
   * Flattens this maybe into something from its left or right leaning
   * positions
   *
   * @param callable $onfailure (left)
   * @param callable $onsuccess (right)
   * @return mixed
   */
  public function fold($onfailure, $onsuccess) {
    return $this->isRight() ?
      $onsuccess($this->get()) :
      $onfailure($this->get());
  }

  /**
   * Create a projection that is only operable on right leaning maybe's
   *
   * @return Right_Projection
   */
  public function withRight() {
    return new Right_Projection($this);
  }

  /**
   * Create a projection that is only operable on left leaning  maybe's
   *
   * @return Left_Projection
   */
  public function withLeft() {
    return new Left_Projection($this);
  }
}

/**
 * A projection is a proxy object for maybes that allows
 * Monadic operations on the underying Model_Either.
 *
 * Other than the Monadic calls, all other calls are forwarded to
 * the underlying object.
 */
abstract class Either_Projection implements Monadic {
  protected $identity;

  /**
   * Does this projection accept operations of this maybe?
   *
   * @return boolean
   */
  public abstract function isAcceptable();

  /**
   * Re-wraps the projection with its appropriate maybe
   *
   * @param mixed $result
   * @return Either_Projection
   */
  public abstract function wrap($result);

  /**
   * Constructs this projection off of a maybe type
   *
   * @param Model_Either $either
   */
  public function __construct(Model_Either $either) {
    $this->identity = $either;
  }

  /**
   * Forwards calls to the underlying maybe type
   *
   * @param string $name
   * @param array $args
   */
  public function __call($name, $args = array()) {
    return call_user_func_array(array($this->identity, $name), $args);
  }

  /**
   * @see parent
   * @return Either_Projection
   */
  public function map($callback) {
    if ($this->isAcceptable()) {
      return $this->wrap($callback($this->get()));
    } else {
      return $this;
    }
  }

  /**
   * @see parent
   * @return Either_Projection
   */
  public function each($callback) {
    if ($this->isAcceptable()) {
      $callback($this->get());
    }
    return $this;
  }

  /**
   * @see parent
   * @return Either_Projection
   */
  public function filter($callback) {
    if ($this->isAcceptable() && $callback($this->get())) {
      return new Model_Some($this->wrap($this->get()));
    }
    return new Model_None();
  }
}

/**
 * A maybe type that is left leaning
 */
class Model_Left extends Model_Either {
  /**
   * @see parent
   * @return boolean
   */
  public function isRight() {
    return false;
  }
}

/**
 * A maybe type that is right leaning
 */
class Model_Right extends Model_Either {
  /**
   * @see parent
   * @return boolean
   */
  public function isRight() {
    return true;
  }
}
/**
 * A projection that is left leaning
 */
class Left_Projection extends Either_Projection {
  /**
   * Only accepts operations on a Model_Left
   *
   * @return boolean
   */
  public function isAcceptable() {
    return $this->isLeft();
  }

  /**
   * @see parent
   *
   * @param mixed $result
   * @return Left_Projection
   */
  public function wrap($result) {
    return new Left_Projection(new Model_Left($result));
  }
}

/**
 * A projection that is right leaning
 */
class Right_Projection extends Either_Projection {
  /**
   * Only accepts operations on a Model_Right
   *
   * @return boolean
   */
  public function isAcceptable() {
    return $this->isRight();
  }

  /**
   * @see parent
   *
   * @param mixed $result
   * @return Right_Projection
   */
  public function wrap($result) {
    return new Right_Projection(new Model_Right($result));
  }
}
