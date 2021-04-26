<?php

namespace Drupal\themoviedb_import\Commands;

use Drush\Commands\DrushCommands;
use Drupal\themoviedb_import\Controller\ThemoviedbController;

/**
 * A drush command file.
 *
 * @package Drupal\themoviedb_import\Commands
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
  public function import($text) {
    $theMovie = new ThemoviedbController();
    $theMovie->ImportData();

    $this->output()->writeln("Data as saved");
  }

}
