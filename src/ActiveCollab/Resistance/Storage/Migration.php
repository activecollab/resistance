<?php
  namespace ActiveCollab\Resistance\Storage;

  use Predis\Transaction\MultiExec;

  /**
   * @package ActiveCollab\Resistance\Storage
   */
  abstract class Migration
  {
    /**
     * @param MultiExec $t
     */
    abstract public function up(MultiExec $t);
  }