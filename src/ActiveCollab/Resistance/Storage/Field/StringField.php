<?php
  namespace ActiveCollab\Resistance\Storage\Field;

  use ActiveCollab\Resistance\Error\Error;
  use ActiveCollab\Resistance\Error\ValidationError;

  /**
   * @package ActiveCollab\Resistance\Storage\Field
   */
  class StringField extends Field
  {
    const FORMAT_ID = '/^\d+$/';
    const FORMAT_WORD = '/^\w+$/';
    const FORMAT_SLUG = '/^[a-z0-9\-\._]+$/';
    const FORMAT_HASH = '/^[a-z0-9]{40}$/';
    const FORMAT_DATE = '/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])$/';

    /**
     * @param  mixed $value
     * @return string
     */
    public function cast($value)
    {
      $value = (string) $value;

      if ($this->modifier) {
        $value = call_user_func($this->modifier, $value);
      }

      return $value;
    }

    /**
     * Validate that value conforms the given format
     *
     * @param  string $format
     * @return $this
     */
    public function &format($format)
    {
      if ($format) {
        $this->setValidator('string_format', function ($field_name, $value) use ($format) {
          if (!$value || !preg_match($format, $value)) {
            throw new ValidationError("Value '$value' of '$field_name' does not match '$format'");
          }
        });
      } else {
        $this->setValidator('string_format', null);
      }

      return $this;
    }

    /**
     * Make sure that value inserted in this record is a valid URL
     *
     * @return $this
     */
    public function &isUrl()
    {
      $this->setValidator('string_is_url', function ($field_name, $value) {
        if (!$value || !filter_var($value, FILTER_VALIDATE_URL)) {
          throw new ValidationError("Value '$value' of '$field_name' is not a valid URL");
        }
      });

      return $this;
    }

    /**
     * Make sure that value inserted in this record is a valid email address
     *
     * @return $this
     */
    public function &isEmail()
    {
      $this->setValidator('string_is_email', function ($field_name, $value) {
        if (!$value || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
          throw new ValidationError("Value '$value' of '$field_name' is not a valid email address");
        }
      });

      return $this;
    }


    /**
     * @var string
     */
    private $modifier;

    /**
     * @param  callable|null $modifier
     * @return $this
     * @throws Error
     */
    public function &modifier(callable $modifier = null)
    {
      if (is_callable($modifier) || $modifier === null) {
        $this->modifier = $modifier;
      } else {
        throw new Error("Function '$modifier' does not exist");
      }

      return $this;
    }
  }