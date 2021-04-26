<?php

namespace Drupal\drush9_custom_commands\Commands;

use Drush\Commands\DrushCommands;

/**
 * A drush command file.
 *
 * @package Drupal\drush9_custom_commands\Commands
 */
class importDrupalCommand extends DrushCommands {

  /**
   * Drush command that displays the given text.
   *
   * @param string $text
   *   Argument with message to be displayed.
   * @command themoviedb:import
   * @aliases themimport
   * @option uppercase
   *   Uppercase the message.
   * @option reverse
   *   Reverse the message.
   * @usage themoviedb:import
   */
  public function message($text) {



    $this->output()->writeln($text);
  }

}
